<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService;

use Dbp\CampusonlineApi\Helpers\Filters;
use Dbp\CampusonlineApi\Helpers\Page;
use Dbp\CampusonlineApi\Helpers\Pagination;
use League\Uri\Contracts\UriException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use SimpleXMLElement;
use SimpleXMLIterator;

abstract class ResourceApi
{
    public const FILTERS_OPTION = 'filters';

    protected const GET_CHILD_IDS_OPTION_KEY = 'get_child_ids';

    private const FILTER_ATTRIBUTE_OPERATOR = 'operator';
    private const FILTER_ATTRIBUTE_FILTER_VALUE = 'filterValue';
    private const FILTER_ATTRIBUTE_LOGICAL_OPERATOR = 'logical';

    /** @var string */
    protected $rootOrgUnitId;

    /** @var string[] */
    private $attributeNameToXpathMap;

    /** @var string|null */
    private $resourceXpathExpression;

    /** @var Connection */
    private $connection;

    /** @var CacheItemPoolInterface */
    private $cache;

    /** @var int */
    private $cacheTtl = 0;

    public static function addFilter(array &$targetOptions, string $fieldName, string $operator, $filterValue, string $logicalOperator = Filters::LOGICAL_AND_OPERATOR)
    {
        if (isset($targetOptions[self::FILTERS_OPTION]) === false) {
            $targetOptions[self::FILTERS_OPTION] = [];
        }

        $targetOptions[self::FILTERS_OPTION][$fieldName] = [
            self::FILTER_ATTRIBUTE_OPERATOR => $operator,
            self::FILTER_ATTRIBUTE_FILTER_VALUE => $filterValue,
            self::FILTER_ATTRIBUTE_LOGICAL_OPERATOR => $logicalOperator,
        ];
    }

    public static function getResourcePropertyOrEmptyString(SimpleXMLElement $node, string $xmlPath): string
    {
        return trim((string) ($node->xpath($xmlPath)[0] ?? ''));
    }

    protected static function addEqualsIdFilter(array &$targetOptions, string $identifier)
    {
        self::addFilter($targetOptions, ResourceData::IDENTIFIER_ATTRIBUTE, Filters::EQUALS_OPERATOR, $identifier, Filters::LOGICAL_AND_OPERATOR);
    }

    protected static function hasEqualsIdFilter(array $options): bool
    {
        if (($filters = $options[self::FILTERS_OPTION] ?? null) !== null) {
            if (($idFilter = $filters[ResourceData::IDENTIFIER_ATTRIBUTE] ?? null) !== null) {
                return
                    ($idFilter[self::FILTER_ATTRIBUTE_OPERATOR] ?? null) === Filters::EQUALS_OPERATOR &&
                    ($idFilter[self::FILTER_ATTRIBUTE_LOGICAL_OPERATOR] ?? null) === Filters::LOGICAL_AND_OPERATOR;
            }
        }

        return false;
    }

    protected static function getResourceDataFromXmlStatic(SimpleXMLElement $node, array $attributeNameToXpathMap): array
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

        foreach ($filters as $attributeName => $filter) {
            $stringValue = $currentResourceItem->getData()[$attributeName];
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

    public function setCache(CacheItemPoolInterface $cache, int $cacheTtl)
    {
        $this->cache = $cache;
        $this->cacheTtl = $cacheTtl;
    }

    /**
     * Tries to check if the service is reachable and the authorization works in a reasonable time.
     * Will throw if the service isn't responding as expected.
     */
    abstract public function checkConnection(); // make sure this doesn't take long with lots of data provided by the API

    protected function __construct(Connection $connection, string $rootOrgUnitId, array $attributeNameToXpathMap, string $resourceXpathExpression = null)
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
        $resourceCacheItem = $this->getCacheItem($identifier);

        return $resourceCacheItem->isHit() ? $resourceCacheItem->get() : null;
    }

    protected function getPage(string $uri, array $uriParameters, array $options): Page
    {
        $resourceIdentifiers = $this->getResultIdentifiersCached($uri, $uriParameters, $options);

        return $this->filterResources($resourceIdentifiers, $options);
    }

    protected function getResourceDataFromXml(SimpleXMLElement $node): array
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
            $currentResourceCacheItem = $this->getCacheItem($resourceIdentifier);
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

    protected function isResourceNode(SimpleXMLElement $node): bool
    {
        return false;
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
        if ($resultIdentifiersCacheItem->isHit() === false) {
            $resultIdentifiers = [];
            foreach ($this->getResources($uri, $uriParameters, $options) as $resourceItem) {
                $resourceCacheItem = $this->getCacheItem($resourceItem->getIdentifier());
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
            $xml = new SimpleXMLElement($responseBody);
        } catch (\Exception $e) {
            throw new ApiException('response body is not in valid XML format');
        }

        $resourceItems = [];
        if ($this->resourceXpathExpression !== null) {
            $resourceNodes = $xml->xpath($this->resourceXpathExpression);
            if (count($resourceNodes) > 0) {
                foreach ($resourceNodes as $resourceNode) {
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
            $xml = new SimpleXMLIterator($responseBody);
        } catch (\Exception $e) {
            throw new ApiException('response body is not in valid XML format');
        }

        $resourceItems = [];
        $childIds = [];
        $this->addChildResourceItems($xml, null, $resourceItems, $childIds);

        return $resourceItems;
    }

    private function addChildResourceItems(SimpleXMLIterator $iterator, ?string $parentIdentifier, array &$resultItems, array &$childIdentifiers)
    {
        for ($iterator->rewind(); $iterator->valid(); $iterator->next()) {
            $child = $iterator->current();
            if ($child !== null && $this->isResourceNode($child)) {
                $resultItem = $this->createResource();
                $resultItem->setData($this->getResourceDataFromXml($child));
                $resultItem->setParentIdentifier($parentIdentifier);
                $resultItems[] = $resultItem;
                $resultItemIdentifier = $resultItem->getIdentifier();
                $childIdentifiers[] = $resultItemIdentifier;

                $grandChildIdentifiers = [];
                $this->addChildResourceItems($child, $resultItemIdentifier, $resultItems, $grandChildIdentifiers);
                $resultItem->setChildIdentifiers($grandChildIdentifiers);
            }
        }
    }

    /**
     * @throws ApiException
     */
    private function getCacheItem(string $rawKey): CacheItemInterface
    {
        if ($this->cache === null) {
            throw new ApiException('cache is not set');
        }

        try {
            return $this->cache->getItem(urlencode($rawKey));
        } catch (InvalidArgumentException $e) {
            throw new ApiException('invalid cache key');
        }
    }

    private function saveCacheItem(CacheItemInterface $resourceCacheItem, $resourceItem)
    {
        if ($this->cache === null) {
            throw new ApiException('cache is not set');
        }

        $resourceCacheItem->set($resourceItem);
        $resourceCacheItem->expiresAfter($this->cacheTtl);
        $this->cache->save($resourceCacheItem);
    }
}
