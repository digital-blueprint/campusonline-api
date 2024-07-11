<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService;

class ResourceData
{
    public const IDENTIFIER_ATTRIBUTE = 'identifier';
    public const CHILD_IDENTIFIERS_ATTRIBUTE = 'childIds';
    public const PARENT_IDENTIFIER_ATTRIBUTE = 'parentId';

    protected array $data = [];

    public function __construct()
    {
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function setAttribute(string $attributeName, $attributeValue): void
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

    public function getChildIdentifiers(): array
    {
        return $this->data[self::CHILD_IDENTIFIERS_ATTRIBUTE];
    }

    public function setChildIdentifiers(array $childIds): void
    {
        $this->data[self::CHILD_IDENTIFIERS_ATTRIBUTE] = $childIds;
    }

    public function setParentIdentifier(?string $parentIdentifier): void
    {
        $this->data[self::PARENT_IDENTIFIER_ATTRIBUTE] = $parentIdentifier;
    }

    public function getParentIdentifier(): string
    {
        return $this->data[self::PARENT_IDENTIFIER_ATTRIBUTE];
    }
}
