<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Helpers;

class Options
{
    public const CURRENT_PAGE_NUMBER = Pagination::CURRENT_PAGE_NUMBER_PARAMETER_NAME;
    public const MAX_NUM_ITEMS_PER_PAGE = Pagination::MAX_NUM_ITEMS_PER_PAGE_PARAMETER_NAME;
    public const FORCE_CACHE_MISS = 'force_cache_miss';
}
