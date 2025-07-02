<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Organizations;

class OrganizationResource
{
    private const UID_ATTRIBUTE = 'uid';
    private const CODE_ATTRIBUTE = 'code';
    private const PARENT_UID_ATTRIBUTE = 'parentUid';
    private const NAME_ATTRIBUTE = 'name';
    private const GROUP_KEY_ATTRIBUTE = 'groupKey';
    private const TYPE_ATTRIBUTE = 'type';

    private const TYPE_UID_ATTRIBUTE = 'uid';
    private const TYPE_NAME_ATTRIBUTE = 'name';
    private const NAME_VALUE_ATTRIBUTE = 'value';

    public function __construct(
        private readonly array $organizationResourceData)
    {
    }

    public function getResourceData(): array
    {
        return $this->organizationResourceData;
    }

    public function getUid(): ?string
    {
        return $this->organizationResourceData[self::UID_ATTRIBUTE] ?? null;
    }

    public function getCode(): ?string
    {
        return $this->organizationResourceData[self::CODE_ATTRIBUTE] ?? null;
    }

    public function getParentUid(): ?string
    {
        return $this->organizationResourceData[self::PARENT_UID_ATTRIBUTE] ?? null;
    }

    public function getName(): ?array
    {
        return $this->organizationResourceData[self::NAME_ATTRIBUTE][self::NAME_VALUE_ATTRIBUTE] ?? null;
    }

    public function getGroupKey(): ?string
    {
        return $this->organizationResourceData[self::GROUP_KEY_ATTRIBUTE] ?? null;
    }

    public function getTypeUid(): ?string
    {
        return $this->organizationResourceData[self::TYPE_ATTRIBUTE][self::TYPE_UID_ATTRIBUTE] ?? null;
    }

    public function getTypeName(): ?array
    {
        return $this->organizationResourceData[self::TYPE_ATTRIBUTE][self::TYPE_NAME_ATTRIBUTE][self::NAME_VALUE_ATTRIBUTE] ?? null;
    }
}
