<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Helpers;

class Pagination
{
    public const CURRENT_PAGE_NUMBER_PARAMETER_NAME = 'page';
    public const MAX_NUM_ITEMS_PER_PAGE_PARAMETER_NAME = 'perPage';

    public const ALL_ITEMS = -1;

    public static function getPageStartIndex(int $pageNumber, int $maxNumItemsPerPage)
    {
        return ($pageNumber - 1) * $maxNumItemsPerPage;
    }

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

        return $numItemsPerPage ? self::getPageStartIndex($page, $numItemsPerPage) : 0;
    }

    public static function getMaxNumItemsPerPage(array $options, int $default = self::ALL_ITEMS): int
    {
        return self::getMaxNumItemsPerPageInternal($options) ?? $default;
    }

    /**
     * If num items per page is not provided, we use the total number of items.
     */
    public static function createEmptyPage(array $options): Page
    {
        $numItemsPerPage = self::getMaxNumItemsPerPage($options, 0);

        return new Page([], 1, $numItemsPerPage);
    }

    /**
     * If num items per page is not provided, we use the total number of result items.
     */
    public static function createPage(array $pageItems, array $options): Page
    {
        $currentPageNum = self::getCurrentPageNumberInternal($options);
        $maxNumItemsPerPage = self::getMaxNumItemsPerPage($options, count($pageItems));

        return new Page($pageItems, $currentPageNum, $maxNumItemsPerPage);
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
