<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Organizations;

use Dbp\CampusonlineApi\PublicRestApi\Resource;

class OrganizationResource extends Resource
{
    private const CODE_ATTRIBUTE = 'code';
    private const PARENT_UID_ATTRIBUTE = 'parentUid';
    private const NAME_ATTRIBUTE = 'name';
    private const GROUP_KEY_ATTRIBUTE = 'groupKey';
    private const TYPE_ATTRIBUTE = 'type';

    private const TYPE_UID_ATTRIBUTE = 'uid';
    private const TYPE_NAME_ATTRIBUTE = 'name';
    private const NAME_VALUE_ATTRIBUTE = 'value';
    private const CONTACT_INFO_ATTRIBUTE = 'contactInfo';
    private const ADDRESS_ATTRIBUTE = 'address';
    private const STREET_ATTRIBUTE = 'street';
    private const CITY_ATTRIBUTE = 'city';
    private const POSTAL_CODE_ATTRIBUTE = 'postalCode';
    private const COUNTRY_ATTRIBUTE = 'country';

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

    public function getNumberOfContactInfos(): int
    {
        return count($this->resourceData[self::CONTACT_INFO_ATTRIBUTE] ?? []);
    }

    public function getAddressStreet(int $contactInfoIndex = 0): ?string
    {
        return $this->resourceData[self::CONTACT_INFO_ATTRIBUTE][$contactInfoIndex][self::ADDRESS_ATTRIBUTE][self::STREET_ATTRIBUTE] ?? null;
    }

    public function getAddressCity(int $contactInfoIndex = 0): ?string
    {
        return $this->resourceData[self::CONTACT_INFO_ATTRIBUTE][$contactInfoIndex][self::ADDRESS_ATTRIBUTE][self::CITY_ATTRIBUTE] ?? null;
    }

    public function getAddressPostalCode(int $contactInfoIndex = 0): ?string
    {
        return $this->resourceData[self::CONTACT_INFO_ATTRIBUTE][$contactInfoIndex][self::ADDRESS_ATTRIBUTE][self::POSTAL_CODE_ATTRIBUTE] ?? null;
    }

    public function getAddressCountry(int $contactInfoIndex = 0): ?string
    {
        return $this->resourceData[self::CONTACT_INFO_ATTRIBUTE][$contactInfoIndex][self::ADDRESS_ATTRIBUTE][self::COUNTRY_ATTRIBUTE] ?? null;
    }
}
