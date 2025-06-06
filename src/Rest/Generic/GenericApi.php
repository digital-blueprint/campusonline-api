<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Rest\Generic;

use Dbp\CampusonlineApi\Helpers\ApiException;
use Dbp\CampusonlineApi\Helpers\Pagination;
use Dbp\CampusonlineApi\Rest\Connection;
use Dbp\CampusonlineApi\Rest\FilterBuilder;
use Dbp\CampusonlineApi\Rest\Tools;
use GuzzleHttp\Exception\GuzzleException;
use League\Uri\UriTemplate;
use Psr\Http\Message\ResponseInterface;

/**
 * A generic API wrapper for custom exports.
 */
class GenericApi
{
    /**
     * @var string
     */
    private $dataService;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection, string $dataService)
    {
        $this->connection = $connection;
        $this->dataService = $dataService;
    }

    /**
     * Returns a single resource by filtering on a unique field.
     *
     * This requires that the field that is used for filtering is unique.
     *
     * @param string  $field    The field to filter against (usually 'ID')
     * @param string  $value    The requests value of the field
     * @param ?string $language The requested language of the response
     */
    public function getResource(string $field, string $value, ?string $language = null): ?ApiResource
    {
        $filters = (new FilterBuilder())->eq($field, $value)->getFilters();
        $collection = $this->getResourceCollection($filters, 0, 2, $language);

        if (count($collection) >= 1) {
            if ((string) $collection[0]->content[$field] === $value) {
                // we got one correct result, yay
                if (count($collection) === 1) {
                    return $collection[0];
                }
                // we got correct results, but too many
                throw new ApiException("Filter on '$field' with '$value' returned multiple results while only <=1 is allowed");
            }

            // we got bogus results because the field type didn't match, so likely the backend tried to return everything
            return null;
        }

        // we got no results
        return null;
    }

    /**
     * Returns an array of API resources.
     *
     * @param array   $filters  An array of filters to apply
     * @param int     $skip     How many items of the result to skip
     * @param int     $top      How many items to return at a maximum. -1 means as many as possible.
     *                          Note that the API might return fewer results as requested and available.
     * @param ?string $language The requested language of the response
     *
     * @return ApiResource[]
     */
    public function getResourceCollection(array $filters = [], int $skip = 0, int $top = -1, ?string $language = null): array
    {
        $connection = $this->connection;
        $dataService = $connection->getDataServiceId($this->dataService);
        $uriTemplate = new UriTemplate('pl/rest/{service}/{?%24filter,%24format,%24top,%24skip}');
        $vars = [
            'service' => $dataService,
            '%24filter' => implode(';', $filters),
            '%24format' => 'json',
            '%24skip' => $skip,
            '%24top' => $top,
        ];
        $uri = (string) $uriTemplate->expand($vars);

        $extraOptions = [];
        if ($language !== null) {
            $extraOptions['headers']['Accept-Language'] = $language;
        }

        $client = $connection->getClient();
        try {
            $response = $client->get($uri, $extraOptions);
        } catch (GuzzleException $guzzleException) {
            throw ApiException::fromGuzzleException($guzzleException);
        }

        return $this->parseResourceList($response);
    }

    /**
     * Returns a page of API resources.
     *
     * Compared to getResourceCollection() this will always return all available items for the requested range
     * and potentially make multiple requests internally.
     *
     * @param array   $filters  And array of filters to apply
     * @param int     $page     The page to return, starting with 1
     * @param int     $perPage  The amount of items per page
     * @param ?string $language The requested language of the response
     *
     * @return ApiResource[]
     */
    public function getResourcePage(array $filters, int $page, int $perPage, ?string $language = null): array
    {
        $top = $perPage;
        $skip = Pagination::getPageStartIndex($page, $perPage);

        $allItems = [];
        while (true) {
            $newItems = $this->getResourceCollection($filters, $skip, $top, $language);
            if (count($newItems) === 0) {
                break;
            }
            $allItems = array_merge($allItems, $newItems);
            if (count($allItems) === $perPage) {
                break;
            } elseif (count($allItems) > $perPage) {
                // In case CO returns more than we wanted, which in theory shouldn't happen
                $allItems = array_slice($allItems, 0, $perPage);
                break;
            }
            $skip += count($newItems);
            $top -= count($newItems);
        }

        return $allItems;
    }

    /**
     * @return ApiResource[]
     *
     * @throws \JsonException
     */
    private function parseResourceList(ResponseInterface $response): array
    {
        $resourceData = Tools::decodeJSON((string) $response->getBody(), true);

        $resultList = [];
        foreach ($resourceData['resource'] as $resource) {
            $content = $resource['content'];
            $type = $content['type'];
            $parts = explode('.', $type);
            $elementName = end($parts);

            $result = new ApiResource();
            $result->type = $type;
            // XXX: I couldn't find in the docs if the element is always named that way, so fail loudly if
            // my assumption was wrong.
            if (!array_key_exists($elementName, $content)) {
                throw new \RuntimeException('content missing');
            }
            $result->content = $content[$elementName];
            $resultList[] = $result;
        }

        return $resultList;
    }
}
