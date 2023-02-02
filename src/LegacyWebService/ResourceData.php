<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService;

class ResourceData
{
    public const IDENTIFIER_ATTRIBUTE = 'identifier';

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

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function setAttribute(string $attributeName, $attributeValue)
    {
        $this->data[$attributeName] = $attributeValue;
    }

    public function getIdentifier(): string
    {
        return $this->data[self::IDENTIFIER_ATTRIBUTE];
    }

    public function setIdentifier(string $identifier): void
    {
        $this->data[self::IDENTIFIER_ATTRIBUTE] = $identifier;
    }
}
