<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService;

use Dbp\CampusonlineApi\Helpers\Filters;
use Dbp\CampusonlineApi\Helpers\Page;
use Dbp\CampusonlineApi\Helpers\Pagination;
use SimpleXMLElement;

abstract class ResourceApi
{
    public const FILTERS_OPTION = 'filters';

    private const FILTER_ATTRIBUTE_OPERATOR = 'operator';
    private const FILTER_ATTRIBUTE_FILTER_VALUE = 'filterValue';
    private const FILTER_ATTRIBUTE_LOGICAL_OPERATOR = 'logical';

    /** @var string */
    protected $rootOrgUnitId;

    /** @var string */
    private $resourceXmlPath;

    /** @var Connection */
    private $connection;

    public static function addIdFilter(array &$targetOptions, string $identifier)
    {
        self::addFilter($targetOptions, ResourceData::IDENTIFIER_ATTRIBUTE, Filters::EQUALS_OPERATOR, $identifier);
    }

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

    /**
     * @return array|false The resource data in case it passes all given filters; false otherwise
     */
    protected static function getResourceDataFromXmlIfPassesFiltersStatic(SimpleXMLElement $node, array $attributeNameToXpathExpressionMapping, array $filters = [])
    {
        $data = [];
        // tri-state: null (no 'or' filter applied), true (at least one 'or' filter passed), false (none of the 'or' filters passed)
        $didAnyLogicalOrFilterPass = null;

        foreach ($attributeNameToXpathExpressionMapping as $attributeName => $xpathExpression) {
            $stringValue = self::getResourcePropertyOrEmptyString($node, $xpathExpression);
            if (($filter = $filters[$attributeName] ?? null) !== null) {
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
            $data[$attributeName] = $stringValue;
        }

        if ($didAnyLogicalOrFilterPass === false) {
            return false;
        }

        return $data;
    }

    /**
     * Tries to check if the service is reachable and the authorization works in a reasonable time.
     * Will throw if the service isn't responding as expected.
     */
    abstract public function checkConnection(); // make sure this doesn't take long with lots of data provided by the API

    protected function __construct(Connection $connection, string $rootOrgUnitId, string $resourceXmlPath)
    {
        $this->resourceXmlPath = $resourceXmlPath;

        $this->connection = $connection;
        $this->rootOrgUnitId = $rootOrgUnitId;
    }

    /**
     * Check if the API responds with the given error for the given parameters.
     * Useful for checkConnection().
     */
    protected function expectGetError(string $uri, array $parameters, int $statusCode): void
    {
        try {
            // disable caching, so we don't get a stale response
            $this->connection->get($uri, '', $parameters, false);
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
    protected function getResourcesInternal(string $uri, array $parameters, array $options): Page
    {
        $responseBody = $this->connection->get(
            $uri, $options[Api::LANGUAGE_PARAMETER_NAME] ?? '', $parameters);

        return $this->parseResponse($responseBody, $options);
    }

    /**
     * @throws ApiException
     */
    protected function parseResponse(string $responseBody, array $options): Page
    {
        try {
            $xml = new SimpleXMLElement($responseBody);
        } catch (\Exception $e) {
            throw new ApiException('response body is not in valid XML format');
        }
        $nodes = $xml->xpath($this->resourceXmlPath);

        $totalNumItems = count($nodes);
        $numItemsPerPage = Pagination::getMaxNumItemsPerPage($options, $totalNumItems);
        $firstMatchingItemsIndex = Pagination::getCurrentPageStartIndex($options);
        $matchingItemCount = 0;
        $filters = $options[self::FILTERS_OPTION] ?? [];

        $resources = [];
        for ($nodeIndex = 0; $nodeIndex < $totalNumItems; ++$nodeIndex) {
            $node = $nodes[$nodeIndex];

            if (($resourceData = $this->getResourceDataFromXmlIfPassesFilters($node,
                    $this->getAttributeNameToXpathExpressionMapping(), $filters)) !== false) {
                ++$matchingItemCount;
                if ($matchingItemCount > $firstMatchingItemsIndex && ($numItemsPerPage === $totalNumItems || count($resources) < $numItemsPerPage)) {
                    $resource = $this->createResource($node);
                    $resource->setData($resourceData);
                    $resources[] = $resource;

                    if (count($resources) === $numItemsPerPage) {
                        break;
                    }
                }
            }
        }

        return Pagination::createPage($resources, $options);
    }

    /**
     * @return array|false The resource data in case it passes all given filters; false otherwise
     */
    protected function getResourceDataFromXmlIfPassesFilters(SimpleXMLElement $node, array $attributeNameToXpathExpressionMapping, array $filters = [])
    {
        return self::getResourceDataFromXmlIfPassesFiltersStatic($node, $attributeNameToXpathExpressionMapping, $filters);
    }

    abstract protected function createResource(SimpleXMLElement $node): ResourceData;

    abstract protected function getAttributeNameToXpathExpressionMapping(): array;
}
