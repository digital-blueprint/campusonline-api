<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Helpers;

class Paginator
{
    /** @var array */
    private $items;
    /** @var int */
    private $currentPageNumber;
    /** @var int */
    private $maxNumItemsPerPage;

    public function __construct(array $items, int $currentPageNumber, int $maxNumItemsPerPage)
    {
        $this->items = $items;
        $this->currentPageNumber = $currentPageNumber;
        $this->maxNumItemsPerPage = $maxNumItemsPerPage;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getCurrentPageNumber(): int
    {
        return $this->currentPageNumber;
    }

    public function getMaxNumItemsPerPage(): int
    {
        return $this->maxNumItemsPerPage;
    }
}
