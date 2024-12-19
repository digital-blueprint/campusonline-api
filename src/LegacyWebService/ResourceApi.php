<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService;

use Dbp\CampusonlineApi\Helpers\Filters;
use Dbp\CampusonlineApi\Helpers\Options;
use Dbp\CampusonlineApi\Helpers\Page;
use Dbp\CampusonlineApi\Helpers\Pagination;
use League\Uri\Contracts\UriException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

abstract class ResourceApi
{
    public const FILTERS_OPTION = 'filters';

    protected const GET_CHILD_IDS_OPTION_KEY = 'get_child_ids';

    private const FILTER_ATTRIBUTE_FIELD_NAME = 'field';
    private const FILTER_ATTRIBUTE_OPERATOR = 'operator';
    private const FILTER_ATTRIBUTE_FILTER_VALUE = 'filterValue';
    private const FILTER_ATTRIBUTE_LOGICAL_OPERATOR = 'logical';

    protected string $rootOrgUnitId;
    protected Connection $connection;

    /** @var string[] */
    private array $attributeNameToXpathMap;
    private ?string $resourceXpathExpression;
    private ?CacheItemPoolInterface $cache = null;
    private int $cacheTtl = 0;

    /**
     * @var callable|null
     */
    private $isResourceNodeCallback;

    /**
     * @var callable|null
     */
    private $onRebuildingResourceCacheCallback;

    public static function addFilter(array &$targetOptions, string $fieldName, string $operator, $filterValue,
        string $logicalOperator = Filters::LOGICAL_AND_OPERATOR): void
    {
        if (isset($targetOptions[self::FILTERS_OPTION]) === false) {
            $targetOptions[self::FILTERS_OPTION] = [];
        }

        $targetOptions[self::FILTERS_OPTION][] = [
            self::FILTER_ATTRIBUTE_FIELD_NAME => $fieldName,
            self::FILTER_ATTRIBUTE_OPERATOR => $operator,
            self::FILTER_ATTRIBUTE_FILTER_VALUE => $filterValue,
            self::FILTER_ATTRIBUTE_LOGICAL_OPERATOR => $logicalOperator,
        ];
    }

    public static function getResourcePropertyOrEmptyString(\SimpleXMLElement $node, string $xmlPath): string
    {
        return trim((string) ($node->xpath($xmlPath)[0] ?? ''));
    }

    protected static function addEqualsIdFilter(array &$targetOptions, string $identifier): void
    {
        self::addFilter($targetOptions, ResourceData::IDENTIFIER_ATTRIBUTE, Filters::EQUALS_OPERATOR, $identifier, Filters::LOGICAL_AND_OPERATOR);
    }

    protected static function hasEqualsIdFilter(array $options): bool
    {
        if (($filters = $options[self::FILTERS_OPTION] ?? null) !== null) {
            if (($idFilter = $filters[ResourceData::IDENTIFIER_ATTRIBUTE] ?? null) !== null) {
                return
                    ($idFilter[self::FILTER_ATTRIBUTE_OPERATOR] ?? null) === Filters::EQUALS_OPERATOR
                    && ($idFilter[self::FILTER_ATTRIBUTE_LOGICAL_OPERATOR] ?? null) === Filters::LOGICAL_AND_OPERATOR;
            }
        }

        return false;
    }

    protected static function getResourceDataFromXmlStatic(\SimpleXMLElement $node, array $attributeNameToXpathMap): array
    {
        $data = [];
        foreach ($attributeNameToXpathMap as $attributeName => $xpathExpression) {
            $data[$attributeName] = self::getResourcePropertyOrEmptyString($node, $xpathExpression);
        }

        return $data;
    }

    private static function passesFilters(ResourceData $currentResourceItem, array $filters): bool
    {
        // tri-state: null (no 'or' filter applied), true (at least one 'or' filter passed), false (none of the 'or' filters passed)
        $didAnyLogicalOrFilterPass = null;

        foreach ($filters as $filter) {
            $stringValue = $currentResourceItem->getData()[$filter[self::FILTER_ATTRIBUTE_FIELD_NAME]];
            $logicalOperator = $filter[self::FILTER_ATTRIBUTE_LOGICAL_OPERATOR];
            if ($logicalOperator === Filters::LOGICAL_AND_OPERATOR || $didAnyLogicalOrFilterPass !== true) {
                if (Filters::passesFilter($stringValue, $filter[self::FILTER_ATTRIBUTE_OPERATOR], $filter[self::FILTER_ATTRIBUTE_FILTER_VALUE])) {
                    if ($logicalOperator === Filters::LOGICAL_OR_OPERATOR) {
                        $didAnyLogicalOrFilterPass = true;
                    }
                } elseif ($logicalOperator === Filters::LOGICAL_AND_OPERATOR) {
                    return false;
                } else {
                    $didAnyLogicalOrFilterPass = false;
                }
            }
        }

        return !($didAnyLogicalOrFilterPass === false);
    }

    private static function getResourceItemCacheKey(string $resourceItemIdentifier, array $options): string
    {
        return $resourceItemIdentifier.':'.Connection::getLanguageParameter($options);
    }

    public function setCache(CacheItemPoolInterface $cache, int $cacheTtl): void
    {
        $this->cache = $cache;
        $this->cacheTtl = $cacheTtl;
    }

    /**
     * Tries to check if the service is reachable and the authorization works in a reasonable time.
     * Will throw if the service isn't responding as expected.
     */
    abstract public function checkConnection(); // make sure this doesn't take long with lots of data provided by the API

    public function setIsResourceNodeCallback(callable $isResourceNodeCallback): void
    {
        $this->isResourceNodeCallback = $isResourceNodeCallback;
    }

    public function setOnRebuildingResourceCacheCallback(callable $onRebuildingResourceCacheCallback): void
    {
        $this->onRebuildingResourceCacheCallback = $onRebuildingResourceCacheCallback;
    }

    protected function __construct(Connection $connection, string $rootOrgUnitId, array $attributeNameToXpathMap, ?string $resourceXpathExpression = null)
    {
        $this->connection = $connection;
        $this->rootOrgUnitId = $rootOrgUnitId;
        $this->attributeNameToXpathMap = $attributeNameToXpathMap;
        $this->resourceXpathExpression = $resourceXpathExpression;
    }

    /**
     * Check if the API responds with the given error for the given parameters.
     * Useful for checkConnection().
     */
    protected function expectGetError(string $uri, array $parameters, int $statusCode): void
    {
        try {
            // disable caching, so we don't get a stale response
            $this->connection->get($uri, $parameters, [], false);
        } catch (ApiException $e) {
            if ($e->isHttpResponseCode() && $e->getCode() === $statusCode) {
                return;
            }
            throw $e;
        }
        throw new \RuntimeException("Didn't respond with $statusCode as expected");
    }

    /**
     * @throws ApiException
     */
    protected function getItem(string $identifier, string $uri, array $uriParameters, array $options): ?ResourceData
    {
        $this->getResultIdentifiersCached($uri, $uriParameters, $options);
        $resourceCacheItem = $this->getCacheItem(
            self::getResourceItemCacheKey($identifier, $options));

        return $resourceCacheItem->isHit() ? $resourceCacheItem->get() : null;
    }

    protected function getPage(string $uri, array $uriParameters, array $options): Page
    {
        $resourceIdentifiers = $this->getResultIdentifiersCached($uri, $uriParameters, $options);

        return $this->filterResources($resourceIdentifiers, $options);
    }

    protected function getResourceDataFromXml(\SimpleXMLElement $node): array
    {
        return self::getResourceDataFromXmlStatic($node, $this->attributeNameToXpathMap);
    }

    private function filterResources(array $resourceIdentifiers, array $options): Page
    {
        $filteredResourceItems = [];

        $numItemsPerPage = Pagination::getMaxNumItemsPerPage($options);
        $firstMatchingItemsIndex = Pagination::getCurrentPageStartIndex($options);
        $matchingItemCount = 0;
        $filters = $options[self::FILTERS_OPTION] ?? [];

        foreach ($resourceIdentifiers as $resourceIdentifier) {
            $currentResourceCacheItem = $this->getCacheItem(
                self::getResourceItemCacheKey($resourceIdentifier, $options));
            assert($currentResourceCacheItem->isHit());
            $currentResourceItem = $currentResourceCacheItem->get();

            if (self::passesFilters($currentResourceItem, $filters)) {
                ++$matchingItemCount;

                if ($matchingItemCount > $firstMatchingItemsIndex && ($numItemsPerPage === Pagination::ALL_ITEMS || count($filteredResourceItems) < $numItemsPerPage)) {
                    $filteredResourceItems[] = $currentResourceItem;
                }
            }
        }

        return Pagination::createPage($filteredResourceItems, $options);
    }

    /**
     * @return array The first element tells whether to accept the given node (default: true).
     *               The second element tells whether to check the given node's child nodes (default: true)
     */
    protected function isResourceNode(\SimpleXMLElement $node): array
    {
        $isResourceNode = true;
        $checkChildNodes = true;
        $replacementParentId = null;

        if ($this->isResourceNodeCallback !== null) {
            $func = $this->isResourceNodeCallback;

            $returnValue = $func($node);
            if (!is_array($returnValue)) {
                throw new ApiException('Return value of isResourceNodeCallback must be an array');
            }
            if (count($returnValue) > 0) {
                $isResourceNode = $returnValue[0];
            }
            if (count($returnValue) > 1) {
                $checkChildNodes = $returnValue[1];
            }
            if (count($returnValue) > 2) {
                $replacementParentId = $returnValue[2];
            }
        }

        return [$isResourceNode, $checkChildNodes, $replacementParentId];
    }

    abstract protected function createResource(): ResourceData;

    /**
     * @throws ApiException
     */
    private function getResultIdentifiersCached(string $uri, array $uriParameters, array $options): array
    {
        if ($this->cache === null) {
            throw new ApiException('cache is not available');
        }

        try {
            $uriCacheKey = Connection::makeUri($uri, $uriParameters, $options);
        } catch (UriException $e) {
            throw new ApiException('invalid uri or parameters: '.$uri);
        }

        $resultIdentifiersCacheItem = $this->getCacheItem($uriCacheKey);
        if ($resultIdentifiersCacheItem->isHit() === false || ($options[Options::FORCE_CACHE_MISS] ?? false)) {
            if ($this->onRebuildingResourceCacheCallback !== null) {
                $callback = $this->onRebuildingResourceCacheCallback;
                $callback();
            }

            $resultIdentifiers = [];
            foreach ($this->getResources($uri, $uriParameters, $options) as $resourceItem) {
                $resourceCacheItem = $this->getCacheItem(
                    self::getResourceItemCacheKey($resourceItem->getIdentifier(), $options));
                $this->saveCacheItem($resourceCacheItem, $resourceItem);
                $resultIdentifiers[] = $resourceItem->getIdentifier();
            }
            $this->saveCacheItem($resultIdentifiersCacheItem, $resultIdentifiers);
        }

        return $resultIdentifiersCacheItem->get();
    }

    /**
     * @throws ApiException
     */
    private function getResources(string $uri, array $uriParameters = [], array $options = []): array
    {
        $responseBody = $this->connection->get($uri, $uriParameters, $options);
        if ($options[self::GET_CHILD_IDS_OPTION_KEY] ?? false) {
            return $this->getResourceItemsRecursive($responseBody);
        } else {
            return $this->getResourceItems($responseBody);
        }
    }

    private function getResourceItems(string $responseBody): array
    {
        try {
            $xml = new \SimpleXMLElement($responseBody, LIBXML_NOERROR);
        } catch (\Exception $e) {
            throw new ApiException('response body is not in valid XML format');
        }

        $resourceItems = [];
        if ($this->resourceXpathExpression !== null) {
            foreach ($xml->xpath($this->resourceXpathExpression) as $resourceNode) {
                if ($this->isResourceNode($resourceNode)[0]) {
                    $resourceItem = $this->createResource();
                    $resourceItem->setData($this->getResourceDataFromXml($resourceNode));
                    $resourceItems[] = $resourceItem;
                }
            }
        }

        return $resourceItems;
    }

    private function getResourceItemsRecursive(string $responseBody): array
    {
        try {
            $xml = new \SimpleXMLIterator($responseBody);
        } catch (\Exception $e) {
            throw new ApiException('response body is not in valid XML format');
        }

        $childIds = [];
        $allResourceItems = [];
        $idToReplacementParentIdMap = [];
        $this->addChildResourceItems($xml, null, $childIds, $allResourceItems, $idToReplacementParentIdMap);

        foreach ($idToReplacementParentIdMap as $resourceItemId => $replacementParentId) {
            $parentItem = $allResourceItems[$replacementParentId];
            $childIds = $parentItem->getChildIdentifiers();
            $childIds[] = strval($resourceItemId);
            $parentItem->setChildIdentifiers($childIds);
        }

        return $allResourceItems;
    }

    private function addChildResourceItems(\SimpleXMLIterator $iterator, ?string $parentIdentifier, array &$childIdentifiers, array &$allResourceItems, array &$idToReplacementParentIdMap): void
    {
        for ($iterator->rewind(); $iterator->valid(); $iterator->next()) {
            $child = $iterator->current();
            // phpstorm return type is wrong here
            // @phpstan-ignore notIdentical.alwaysTrue
            if ($child !== null) {
                [$isResourceNode, $checkChildren, $replacementParentId] = $this->isResourceNode($child);
                if ($isResourceNode) {
                    $childItem = $this->createResource();
                    $childItem->setData($this->getResourceDataFromXml($child));
                    $childId = $childItem->getIdentifier();
                    if ($replacementParentId !== null) {
                        $childItem->setParentIdentifier($replacementParentId);
                        $idToReplacementParentIdMap[$childId] = $replacementParentId;
                    } else {
                        $childItem->setParentIdentifier($parentIdentifier);
                        $childIdentifiers[] = $childId;
                    }
                    $allResourceItems[$childId] = $childItem;
                    $grandChildIdentifiers = [];
                    if ($checkChildren) {
                        $this->addChildResourceItems($child, $childId, $grandChildIdentifiers, $allResourceItems, $idToReplacementParentIdMap);
                    }
                    $childItem->setChildIdentifiers($grandChildIdentifiers);
                } elseif ($checkChildren) {
                    $this->addChildResourceItems($child, $parentIdentifier, $childIdentifiers, $allResourceItems, $idToReplacementParentIdMap);
                }
            }
        }
    }

    /**
     * @throws ApiException
     */
    private function getCacheItem(string $key): CacheItemInterface
    {
        if ($this->cache === null) {
            throw new ApiException('cache is not set');
        }

        try {
            return $this->cache->getItem(urlencode($key));
        } catch (InvalidArgumentException $e) {
            throw new ApiException('invalid cache key');
        }
    }

    private function saveCacheItem(CacheItemInterface $resourceCacheItem, $resourceItem): void
    {
        if ($this->cache === null) {
            throw new ApiException('cache is not set');
        }

        $resourceCacheItem->set($resourceItem);
        $resourceCacheItem->expiresAfter($this->cacheTtl);
        $this->cache->save($resourceCacheItem);
    }
}
