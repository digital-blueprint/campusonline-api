<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Helpers;

class Paginator
{
    /** @var array */
    private $items;
    /** @var int */
    private $page;
    /** @var int */
    private $numItemsPerPage;

    public function __construct(array $items, int $page, int $numItemsPerPage)
    {
        $this->items = $items;
        $this->page = $page;
        $this->numItemsPerPage = $numItemsPerPage;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getNumItemsPerPage(): int
    {
        return $this->numItemsPerPage;
    }
}
