<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Helpers;

use Dbp\CampusonlineApi\LegacyWebService\ApiException;

class Pagination
{
    private const CURRENT_PAGE_NUMBER_PARAMETER_NAME = 'page';
    private const MAX_NUM_ITEMS_PER_PAGE_PARAMETER_NAME = 'perPage';
    private const IS_PARTIAL_PAGINATION_PARAMETER_NAME = 'partialPagination';

    private const IS_PARTIAL_PAGINATION_DEFAULT = false;

    /**
     * Provides the indices of the first and the last element of the current page.
     *
     * @throws ApiException
     */
    public static function getCurrentPageStartIndex(array $options): int
    {
        $page = self::getCurrentPageNumberInternal($options);
        if ($page < 1) {
            throw new ApiException(self::CURRENT_PAGE_NUMBER_PARAMETER_NAME.' parameter must be larger than 0');
        }

        $numItemsPerPage = self::getMaxNumItemsPerPageInternal($options);
        if ($numItemsPerPage && $numItemsPerPage < 1) {
            throw new ApiException(self::MAX_NUM_ITEMS_PER_PAGE_PARAMETER_NAME.' parameter must be larger than 0');
        }

        if ($numItemsPerPage === null && $page > 1) {
            throw new ApiException(self::MAX_NUM_ITEMS_PER_PAGE_PARAMETER_NAME.' must be specified when requesting other than first page');
        }

        return $numItemsPerPage ? ($page - 1) * $numItemsPerPage : 0;
    }

    public static function getMaxNumItemsPerPage(array $options, int $default): int
    {
        return self::getMaxNumItemsPerPageInternal($options) ?? $default;
    }

    public static function isPartial(array $options): bool
    {
        return $options[self::IS_PARTIAL_PAGINATION_PARAMETER_NAME] ?? self::IS_PARTIAL_PAGINATION_DEFAULT;
    }

    /**
     * If num items per page is not provided, we use the total number of items.
     */
    public static function createEmptyPaginator(array $options): FullPaginator
    {
        $numItemsPerPage = self::getMaxNumItemsPerPage($options, 0);

        return new FullPaginator([], 1, $numItemsPerPage, 0);
    }

    /**
     * If num items per page is not provided, we use the total number of items.
     */
    public static function createFullPaginator(array $pageItems, int $totalNumItems, array $options): FullPaginator
    {
        $currentPageNum = self::getCurrentPageNumberInternal($options);
        $numItemsPerPage = self::getMaxNumItemsPerPage($options, $totalNumItems);

        return new FullPaginator($pageItems, $currentPageNum, $numItemsPerPage, $totalNumItems);
    }

    /**
     * If num items per page is not provided, we use the total number of items.
     */
    public static function createPaginatorFromWholeResult(array $wholeResultItems, array $options): FullPaginator
    {
        $totalNumItems = count($wholeResultItems);
        $numItemsPerPage = self::getMaxNumItemsPerPage($options, $totalNumItems);

        $pageItems = array_slice($wholeResultItems, Pagination::getCurrentPageStartIndex($options), $numItemsPerPage);

        return Pagination::createFullPaginator($pageItems, $totalNumItems, $options);
    }

    /**
     * If num items per page is not provided, we use the total number of result items.
     */
    public static function createPartialPaginator(array $pageItems, array $options): PartialPaginator
    {
        $currentPageNum = self::getCurrentPageNumberInternal($options);
        $numItemsPerPage = self::getMaxNumItemsPerPage($options, count($pageItems));

        return new PartialPaginator($pageItems, $currentPageNum, $numItemsPerPage);
    }

    private static function getCurrentPageNumberInternal(array $options): int
    {
        return $options[self::CURRENT_PAGE_NUMBER_PARAMETER_NAME] ?? 1;
    }

    private static function getMaxNumItemsPerPageInternal(array $options): ?int
    {
        return $options[self::MAX_NUM_ITEMS_PER_PAGE_PARAMETER_NAME] ?? null;
    }
}
