<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Helpers;

use Dbp\CampusonlineApi\Rest\ApiException;

class Pagination
{
    private const PAGE_PARAMETER_NAME = 'page';
    private const NUM_ITEMS_PER_PAGE_PARAMETER_NAME = 'perPage';

    /**
     * Provides the indices of the first and the last element of the current page for full pagination.
     *
     * @throws ApiException
     */
    public static function getCurrentPageIndicesFull(int $totalNumItems, array $options, int &$firstItemIndex, int &$lastItemIndex): bool
    {
        if ($totalNumItems < 0) {
            throw new ApiException('total num items must be positive');
        }

        $firstItemIndex = -1;
        $lastItemIndex = -1;

        if ($totalNumItems > 0) {
            $page = self::getPage($options, 1);
            if ($page < 1) {
                throw new ApiException(self::PAGE_PARAMETER_NAME.' parameter must be larger than 0');
            }

            $numItemsPerPage = self::getNumItemsPerPage($options, $totalNumItems);
            if ($numItemsPerPage < 1) {
                throw new ApiException(self::NUM_ITEMS_PER_PAGE_PARAMETER_NAME.' parameter must be larger than 0');
            }

            $first = ($page - 1) * $numItemsPerPage;
            if ($first < $totalNumItems) {
                $firstItemIndex = $first;
                $lastItemIndex = min($firstItemIndex + $numItemsPerPage, $totalNumItems) - 1;

                return true;
            }
        }

        return false;
    }

    public static function createFullPaginator(array $items, int $totalNumItems, array $options): FullPaginator
    {
        $page = self::getPage($options, 1);
        $numItemsPerPage = self::getNumItemsPerPage($options, $totalNumItems);

        return new FullPaginator($items, $page, $numItemsPerPage, $totalNumItems);
    }

    private static function getPage(array $options, int $default): int
    {
        return $options[self::PAGE_PARAMETER_NAME] ?? $default;
    }

    private static function getNumItemsPerPage(array $options, int $default): int
    {
        return $options[self::NUM_ITEMS_PER_PAGE_PARAMETER_NAME] ?? $default;
    }
}
