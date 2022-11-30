<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Organization;

use Dbp\CampusonlineApi\LegacyWebService\ResourceData;

class OrganizationUnitData extends ResourceData
{
    public const CODE_ATTRIBUTE = 'code';
    public const TYPE_ATTRIBUTE = 'type';
    public const URL_ATTRIBUTE = 'url';
    public const STREET_ATTRIBUTE = 'streetAddress';
    public const LOCALITY_ATTRIBUTE = 'addressLocality';
    public const POSTAL_CODE_ATTRIBUTE = 'postalCode';
    public const COUNTRY_ATTRIBUTE = 'addressCountry';

    public function getCode(): string
    {
        return $this->data[self::CODE_ATTRIBUTE];
    }

    public function setCode(string $code): void
    {
        $this->data[self::CODE_ATTRIBUTE] = $code;
    }

    public function getType(): string
    {
        return $this->data[self::TYPE_ATTRIBUTE];
    }

    public function setType(string $type): void
    {
        $this->data[self::TYPE_ATTRIBUTE] = $type;
    }

    public function getUrl(): string
    {
        return $this->data[self::URL_ATTRIBUTE];
    }

    public function setUrl(string $url): void
    {
        $this->data[self::URL_ATTRIBUTE] = $url;
    }

    public function getStreet(): string
    {
        return $this->data[self::STREET_ATTRIBUTE];
    }

    public function setStreet(string $street): void
    {
        $this->data[self::STREET_ATTRIBUTE] = $street;
    }

    public function getLocality(): string
    {
        return $this->data[self::LOCALITY_ATTRIBUTE];
    }

    public function setLocality(string $locality): void
    {
        $this->data[self::LOCALITY_ATTRIBUTE] = $locality;
    }

    public function getPostalCode(): string
    {
        return $this->data[self::POSTAL_CODE_ATTRIBUTE];
    }

    public function setPostalCode(string $postalCode): void
    {
        $this->data[self::POSTAL_CODE_ATTRIBUTE] = $postalCode;
    }

    public function getCountry(): string
    {
        return $this->data[self::COUNTRY_ATTRIBUTE];
    }

    public function setCountry(string $country): void
    {
        $this->data[self::COUNTRY_ATTRIBUTE] = $country;
    }

    public function getKindCode(): ?string
    {
        return $this->kindCode;
    }

    public function setKindCode(string $kindCode): void
    {
        $this->kindCode = $kindCode;
    }

    public function getKindName(): ?string
    {
        return $this->kindName;
    }

    public function setKindName(string $kindName): void
    {
        $this->kindName = $kindName;
    }
}
