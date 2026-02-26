<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Organizations;

use Dbp\CampusonlineApi\PublicRestApi\Resource;

class OrganizationResource extends Resource
{
    private const CODE_ATTRIBUTE = 'code';
    private const PARENT_UID_ATTRIBUTE = 'parentUid';
    private const GROUP_KEY_ATTRIBUTE = 'groupKey';
    private const TYPE_ATTRIBUTE = 'type';
    private const TYPE_UID_ATTRIBUTE = 'uid';
    private const TYPE_NAME_ATTRIBUTE = 'name';
    private const NAME_VALUE_ATTRIBUTE = 'value';
    private const SHORT_DESCRIPTION_ATTRIBUTE = 'shortDescription';
    private const CONTACT_INFO_ATTRIBUTE = 'contactInfo';
    private const SORT_ATTRIBUTE = 'sort';
    private const ADDRESS_ATTRIBUTE = 'address';
    private const STREET_ATTRIBUTE = 'street';
    private const CITY_ATTRIBUTE = 'city';
    private const POSTAL_CODE_ATTRIBUTE = 'postalCode';
    private const COUNTRY_ATTRIBUTE = 'country';
    private const EMAIL_ATTRIBUTE = 'email';
    private const TEL_ATTRIBUTE = 'tel';
    private const ADDITIONAL_INFORMATION_ATTRIBUTE = 'additionalInformation';
    private const SEKRETARIAT_INFORMATION_ATTRIBUTE = 'sekretariatInformation';
    private const WEBLINKS_ATTRIBUTE = 'weblinks';
    private const WEB_PAGE_HREF_ATTRIBUTE = 'webPageHref';

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

    public function getContactInfoKey(int $contactInfoIndex = 0): ?string
    {
        return $this->resourceData[self::CONTACT_INFO_ATTRIBUTE][$contactInfoIndex][self::KEY_ATTRIBUTE] ?? null;
    }

    public function getContactInfoSort(int $contactInfoIndex = 0): ?int
    {
        return $this->resourceData[self::CONTACT_INFO_ATTRIBUTE][$contactInfoIndex][self::SORT_ATTRIBUTE] ?? null;
    }

    public function getContactInfoEmail(int $contactInfoIndex = 0): ?string
    {
        return $this->resourceData[self::CONTACT_INFO_ATTRIBUTE][$contactInfoIndex][self::EMAIL_ATTRIBUTE] ?? null;
    }

    public function getContractInfoAddressStreet(int $contactInfoIndex = 0): ?string
    {
        return $this->resourceData[self::CONTACT_INFO_ATTRIBUTE][$contactInfoIndex][self::ADDRESS_ATTRIBUTE][self::STREET_ATTRIBUTE] ?? null;
    }

    public function getContactInfoAddressCity(int $contactInfoIndex = 0): ?string
    {
        return $this->resourceData[self::CONTACT_INFO_ATTRIBUTE][$contactInfoIndex][self::ADDRESS_ATTRIBUTE][self::CITY_ATTRIBUTE] ?? null;
    }

    public function getContactInfoAddressPostalCode(int $contactInfoIndex = 0): ?string
    {
        return $this->resourceData[self::CONTACT_INFO_ATTRIBUTE][$contactInfoIndex][self::ADDRESS_ATTRIBUTE][self::POSTAL_CODE_ATTRIBUTE] ?? null;
    }

    public function getContactInfoAddressCountry(int $contactInfoIndex = 0): ?string
    {
        return $this->resourceData[self::CONTACT_INFO_ATTRIBUTE][$contactInfoIndex][self::ADDRESS_ATTRIBUTE][self::COUNTRY_ATTRIBUTE] ?? null;
    }

    public function getContactInfoTel(int $contactInfoIndex = 0): ?string
    {
        return $this->resourceData[self::CONTACT_INFO_ATTRIBUTE][$contactInfoIndex][self::TEL_ATTRIBUTE] ?? null;
    }

    public function getContactInfoSecretariatInformation(int $contactInfoIndex = 0): ?string
    {
        return $this->resourceData[self::CONTACT_INFO_ATTRIBUTE][$contactInfoIndex][self::ADDITIONAL_INFORMATION_ATTRIBUTE][self::SEKRETARIAT_INFORMATION_ATTRIBUTE] ?? null;
    }

    public function getContactInfoWebPageHref(int $contactInfoIndex = 0): ?string
    {
        return $this->resourceData[self::CONTACT_INFO_ATTRIBUTE][$contactInfoIndex][self::WEBLINKS_ATTRIBUTE][self::WEB_PAGE_HREF_ATTRIBUTE] ?? null;
    }

    public function getShortDescription(): ?string
    {
        return $this->resourceData[self::SHORT_DESCRIPTION_ATTRIBUTE] ?? null;
    }
}
