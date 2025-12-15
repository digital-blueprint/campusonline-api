<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Organizations;

use Dbp\CampusonlineApi\PublicRestApi\Resource;

class OrganizationResource extends Resource
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

    public function getUid(): ?string
    {
        return $this->resourceData[self::UID_ATTRIBUTE] ?? null;
    }

    public function getCode(): ?string
    {
        return $this->resourceData[self::CODE_ATTRIBUTE] ?? null;
    }

    public function getParentUid(): ?string
    {
        return $this->resourceData[self::PARENT_UID_ATTRIBUTE] ?? null;
    }

    public function getName(): ?array
    {
        return $this->resourceData[self::NAME_ATTRIBUTE][self::NAME_VALUE_ATTRIBUTE] ?? null;
    }

    public function getGroupKey(): ?string
    {
        return $this->resourceData[self::GROUP_KEY_ATTRIBUTE] ?? null;
    }

    public function getTypeUid(): ?string
    {
        return $this->resourceData[self::TYPE_ATTRIBUTE][self::TYPE_UID_ATTRIBUTE] ?? null;
    }

    public function getTypeName(): ?array
    {
        return $this->resourceData[self::TYPE_ATTRIBUTE][self::TYPE_NAME_ATTRIBUTE][self::NAME_VALUE_ATTRIBUTE] ?? null;
    }
}
