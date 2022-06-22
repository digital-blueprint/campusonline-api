<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService;

use Dbp\CampusonlineApi\Helpers\Pagination;
use Dbp\CampusonlineApi\Helpers\Paginator;
use SimpleXMLElement;

abstract class ResourceApi
{
    private $resourceXmlPath;
    private $identifierXmlPath;

    protected $connection;
    protected $rootOrgUnitId;

    protected function __construct(Connection $connection, string $rootOrgUnitId, string $resourceXmlPath, string $identifierXmlPath)
    {
        $this->resourceXmlPath = $resourceXmlPath;
        $this->identifierXmlPath = $identifierXmlPath;

        $this->connection = $connection;
        $this->rootOrgUnitId = $rootOrgUnitId;
    }

    /**
     * @throws ApiException
     */
    protected function getResourcesInternal(string $uri, array $parameters, array $options, string $requestedId = ''): Paginator
    {
        $responseBody = $this->connection->get(
            $uri, $options[Api::LANGUAGE_PARAMETER_NAME] ?? '', $parameters);

        return $this->parseResponse($responseBody, $requestedId, $options);
    }

    /**
     * @throws ApiException
     */
    protected function parseResponse(string $responseBody, string $requestedId, array $options): Paginator
    {
        $resources = [];
        $isIdRequested = $requestedId !== '';

        try {
            $xml = new SimpleXMLElement($responseBody);
        } catch (\Exception $e) {
            throw new ApiException('response body is not in valid XML format');
        }
        $nodes = $xml->xpath($this->resourceXmlPath);

        $firstMatchingItemsIndex = 0;
        $isPartialPagination = false;
        $isSearchFilterActive = false;

        if (!$isIdRequested) {
            $firstMatchingItemsIndex = Pagination::getCurrentPageStartIndex($options);
            $isPartialPagination = Pagination::isPartial($options);
            $isSearchFilterActive = $this->isSearchFilterActive($options);
        }

        $totalNumItems = count($nodes);
        $numItemsPerPage = Pagination::getMaxNumItemsPerPage($options, $totalNumItems);
        $matchingItemCount = 0;

        for ($nodeIndex = 0; $nodeIndex < $totalNumItems; ++$nodeIndex) {
            $node = $nodes[$nodeIndex];

            $identifier = null;
            $isMatch = false;

            if ($isIdRequested) {
                if ($this->getResourceIdentifier($node, $identifier) === $requestedId) {
                    $isMatch = true;
                }
            } else {
                $isMatch = !$isSearchFilterActive || $this->passesSearchFilter($node, $options);
            }

            if ($isMatch) {
                ++$matchingItemCount;
                if ($isIdRequested || ($matchingItemCount > $firstMatchingItemsIndex && ($numItemsPerPage === $totalNumItems || count($resources) < $numItemsPerPage))) {
                    $resources[] = $this->createResource($node, $this->getResourceIdentifier($node, $identifier));

                    $done = false;
                    if ($isIdRequested) {
                        $done = true;
                    } elseif (count($resources) === $numItemsPerPage) {
                        if ($isPartialPagination) {
                            $done = true;
                        } elseif (!$isSearchFilterActive) {
                            $done = true;
                            $matchingItemCount = $totalNumItems;
                        }
                    }
                    if ($done) {
                        break;
                    }
                }
            }
        }

        if ($isPartialPagination) {
            return Pagination::createPartialPaginator($resources, $options);
        } else {
            return Pagination::createFullPaginator($resources, $matchingItemCount, $options);
        }
    }

    abstract protected function createResource(SimpleXMLElement $node, string $identifier): object;

    protected function isSearchFilterActive(array $options): bool
    {
        $nameSearchFilter = $options[ResourceData::NAME_SEARCH_FILTER_NAME] ?? '';

        return $nameSearchFilter !== '';
    }

    /**
     * Checks whether the resource passes the given search filters. Performs a partial, case-insensitive text search.
     * Passes if ANY of the given search filters passes.
     */
    protected function passesSearchFilter(SimpleXMLElement $node, array $options): bool
    {
        $nameSearchFilter = $options[ResourceData::NAME_SEARCH_FILTER_NAME] ?? '';

        return $nameSearchFilter !== '' &&
            stripos($this->getResourceName($node), $nameSearchFilter) !== false;
    }

    protected function getResourceName(SimpleXMLElement $node): string
    {
        return '';
    }

    /**
     * @throws ApiException
     */
    protected function getResourceIdentifier(SimpleXMLElement $node, ?string $identifier): string
    {
        $identifier = $identifier ?? self::getResourcePropertyOrEmptyString($node, $this->identifierXmlPath);
        if ($identifier === '') {
            throw new ApiException('ID missing in Campusonline resource');
        }

        return $identifier;
    }

    protected static function getResourcePropertyOrEmptyString(SimpleXMLElement $node, string $xmlPath): string
    {
        return trim((string) ($node->xpath($xmlPath)[0] ?? ''));
    }
}
