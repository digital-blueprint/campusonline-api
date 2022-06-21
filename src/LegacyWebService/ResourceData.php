<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService;

class ResourceData
{
    /** Search filters: Pass, if ANY of the given search filters passes or if NONE is given */

    /** @var string Partial, case-insensitive text search on the 'name' attribute. Passes if filter is empty. */
    public const NAME_SEARCH_FILTER_NAME = 'nameSearchFilter';

    /** @var string */
    private $identifier;

    /** @var string */
    private $name;

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
