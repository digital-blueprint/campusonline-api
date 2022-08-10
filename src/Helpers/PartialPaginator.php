<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Helpers;

class PartialPaginator extends Paginator
{
    public function __construct(array $pageItems, int $currentPageNumber, int $maxNumItemsPerPage)
    {
        parent::__construct($pageItems, $currentPageNumber, $maxNumItemsPerPage);
    }
}
