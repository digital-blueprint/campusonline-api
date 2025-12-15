<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi;

class Resource
{
    public function __construct(
        protected readonly array $resourceData)
    {
    }

    public function getResourceData(): array
    {
        return $this->resourceData;
    }
}
