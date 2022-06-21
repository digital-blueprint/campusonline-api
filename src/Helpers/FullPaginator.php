<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Helpers;

class FullPaginator extends Paginator
{
    /** @var int */
    private $totalNumItems;

    public function __construct(array $items, int $currentPageNumber, int $maxNumItemsPerPage, int $totalNumItems)
    {
        parent::__construct($items, $currentPageNumber, $maxNumItemsPerPage);

        $this->totalNumItems = $totalNumItems;
    }

    public function getTotalNumItems(): int
    {
        return $this->totalNumItems;
    }
}
