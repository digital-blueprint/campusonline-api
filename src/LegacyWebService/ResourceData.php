<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService;

class ResourceData
{
    /** Search filters: Pass, if ANY of the given search filters passes or if NONE is given */
    /** @var string Partial, case-insensitive text search on the 'name' attribute. Passes if filter is empty. */
    public const NAME_SEARCH_FILTER_NAME = 'nameSearchFilter';

    public const IDENTIFIER_ATTRIBUTE = 'identifier';
    public const NAME_ATTRIBUTE = 'name';

    /** @var array */
    protected $data;

    public function __construct()
    {
        $this->data = [];
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getIdentifier(): string
    {
        return $this->data[self::IDENTIFIER_ATTRIBUTE];
    }

    public function setIdentifier(string $identifier): void
    {
        $this->data[self::IDENTIFIER_ATTRIBUTE] = $identifier;
    }

    public function getName(): string
    {
        return $this->data[self::NAME_ATTRIBUTE];
    }

    public function setName(string $name): void
    {
        $this->data[self::NAME_ATTRIBUTE] = $name;
    }
}
