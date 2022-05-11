<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Helpers;

class PartialPaginator extends Paginator
{
    public function __construct(array $items, int $page, int $numItemsPerPage)
    {
        parent::__construct($items, $page, $numItemsPerPage);
    }
}
