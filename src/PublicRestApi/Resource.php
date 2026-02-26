<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi;

class Resource
{
    protected const DEFAULT_LANGUAGE_TAG = 'de';

    protected const UID_ATTRIBUTE = 'uid';
    protected const VALUE_ATTRIBUTE = 'value';
    protected const ITEMS_ATTRIBUTE = 'items';
    protected const KEY_ATTRIBUTE = 'key';
    protected const NAME_ATTRIBUTE = 'name';

    public function __construct(
        protected array $resourceData)
    {
    }

    public function getResourceData(): array
    {
        return $this->resourceData;
    }
}
