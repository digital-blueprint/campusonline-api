<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Helpers;

use Dbp\CampusonlineApi\LegacyWebService\ApiException;

class Pagination
{
    private const PAGE_PARAMETER_NAME = 'page';
    private const NUM_ITEMS_PER_PAGE_PARAMETER_NAME = 'perPage';
    private const IS_PARTIAL_PAGINATION_PARAMETER_NAME = 'partialPagination';

    private const IS_PARTIAL_PAGINATION_DEFAULT = false;

    /**
     * Provides the indices of the first and the last element of the current page.
     *
     * @throws ApiException
     */
    public static function getCurrentPageStartIndex(array $options): int
    {
        $page = self::getPage($options);
        if ($page < 1) {
            throw new ApiException(self::PAGE_PARAMETER_NAME.' parameter must be larger than 0');
        }

        $numItemsPerPage = self::getNumItemsPerPage($options);
        if ($numItemsPerPage && $numItemsPerPage < 1) {
            throw new ApiException(self::NUM_ITEMS_PER_PAGE_PARAMETER_NAME.' parameter must be larger than 0');
        }

        return $numItemsPerPage ? ($page - 1) * $numItemsPerPage : 0;
    }

    public static function getNumItemsPerPage(array $options, int $default = null): ?int
    {
        return $options[self::NUM_ITEMS_PER_PAGE_PARAMETER_NAME] ?? $default;
    }

    public static function isPartial(array $options): bool
    {
        return $options[self::IS_PARTIAL_PAGINATION_PARAMETER_NAME] ?? self::IS_PARTIAL_PAGINATION_DEFAULT;
    }

    /**
     * If num items per page is not provided, we use the total number of items.
     */
    public static function createFullPaginator(array $items, int $totalNumItems, array $options): FullPaginator
    {
        $page = self::getPage($options);
        $numItemsPerPage = self::getNumItemsPerPage($options) ?? $totalNumItems;

        return new FullPaginator($items, $page, $numItemsPerPage, $totalNumItems);
    }

    /**
     * If num items per page is not provided, we use the total number of result items.
     */
    public static function createPartialPaginator(array $items, array $options): PartialPaginator
    {
        $page = self::getPage($options);
        $numItemsPerPage = self::getNumItemsPerPage($options) ?? count($items);

        return new PartialPaginator($items, $page, $numItemsPerPage);
    }

    private static function getPage(array $options): int
    {
        return $options[self::PAGE_PARAMETER_NAME] ?? 1;
    }
}
