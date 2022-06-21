<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Helpers;

class PartialPaginator extends Paginator
{
    public function __construct(array $items, int $currentPageNumber, int $maxNumItemsPerPage)
    {
        parent::__construct($items, $currentPageNumber, $maxNumItemsPerPage);
    }
}
