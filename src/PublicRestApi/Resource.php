<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi;

class Resource
{
    protected const DEFAULT_LANGUAGE_TAG = 'de';

    protected const UID_ATTRIBUTE = 'uid';
    protected const VALUE_ATTRIBUTE = 'value';
    protected const ITEMS_ATTRIBUTE = 'items';

    public function __construct(
        protected readonly array $resourceData)
    {
    }

    public function getResourceData(): array
    {
        return $this->resourceData;
    }
}
