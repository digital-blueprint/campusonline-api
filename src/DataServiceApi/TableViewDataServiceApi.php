<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\DataServiceApi;

use Dbp\CampusonlineApi\Helpers\ApiException;
use Dbp\CampusonlineApi\Helpers\Options;
use Dbp\CampusonlineApi\Helpers\Pagination;

/**
 * A generic API wrapper for custom exports.
 */
readonly class TableViewDataServiceApi extends AbstractDataServiceApi
{
    /**
     * Returns a single resource by filtering on a unique field.
     *
     * This requires that the field that is used for filtering is unique.
     *
     * @param string $field   The field to filter against (usually 'ID')
     * @param string $value   The requests value of the field
     * @param array  $options Additional options for the request. Supported options:
     *                        - Options::LANGUAGE
     */
    public function getResource(string $field, string $value, array $options = []): ?ApiResource
    {
        $filters = (new FilterBuilder())->eq($field, $value)->getFilters();
        $collection = $this->getResourceCollection($filters, 0, 2, $options);

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
     * Returns a collection of API resources.
     *
     * @param array $filters an array of filters as returned by FilterBuilder::getFilters()
     *                       Note that the API might return fewer results as requested and available
     * @param array $options Additional options for the request. Supported options:
     *                       - Options::LANGUAGE
     *
     * @return ApiResource[]
     */
    public function getResourceCollection(
        array $filters = [], int $firstResultIndex = 0, int $maxNumResults = -1, array $options = []): array
    {
        $extraOptions = [];
        if ($language = $options[Options::LANGUAGE] ?? null) {
            $extraOptions['headers']['Accept-Language'] = $language;
        }

        return $this->get('{?%24filter,%24format,%24top,%24skip}',
            [
                '%24filter' => implode(';', $filters),
                '%24format' => 'json',
                '%24skip' => $firstResultIndex,
                '%24top' => $maxNumResults,
            ], $extraOptions);
    }

    /**
     * Returns a page of API resources.
     *
     * Compared to getResourceCollection() this will always return all available items for the requested range
     * and potentially make multiple requests internally.
     *
     * @param array $filters An array of filters as returned by FilterBuilder::getFilters()
     * @param array $options Additional options for the request. Supported options:
     *                       - Options::LANGUAGE
     *
     * @return ApiResource[]
     */
    public function getResourcePage(array $filters, int $pageNumber, int $maxNumItems, array $options = []): array
    {
        $numItemsToGet = $maxNumItems;
        $currentItemIndex = Pagination::getPageStartIndex($pageNumber, $maxNumItems);

        $allItems = [];
        while (true) {
            $newItems = $this->getResourceCollection($filters, $currentItemIndex, $numItemsToGet, $options);
            if (count($newItems) === 0) {
                break;
            }
            $allItems = array_merge($allItems, $newItems);
            if (count($allItems) === $maxNumItems) {
                break;
            } elseif (count($allItems) > $maxNumItems) {
                // In case CO returns more than we wanted, which in theory shouldn't happen
                $allItems = array_slice($allItems, 0, $maxNumItems);
                break;
            }
            $currentItemIndex += count($newItems);
            $numItemsToGet -= count($newItems);
        }

        return $allItems;
    }
}
