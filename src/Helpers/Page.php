<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Helpers;

class Page
{
    /** @var array */
    private $pageItems;
    /** @var int */
    private $currentPageNumber;
    /** @var int */
    private $maxNumItemsPerPage;

    public function __construct(array $pageItems, int $currentPageNumber, int $maxNumItemsPerPage)
    {
        $this->pageItems = $pageItems;
        $this->currentPageNumber = $currentPageNumber;
        $this->maxNumItemsPerPage = $maxNumItemsPerPage;
    }

    public function getItems(): array
    {
        return $this->pageItems;
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
