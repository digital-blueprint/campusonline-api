<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Address;

use Dbp\CampusonlineApi\LegacyWebService\ResourceApi;

class AddressData
{
    /** @var string */
    private $street;

    /** @var string */
    private $city;

    /** @var string */
    private $postalCode;

    /** @var string AT for Austria */
    private $country;

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public static function fromSimpleXmlElement(\SimpleXMLElement $element): AddressData
    {
        $addressData = new AddressData();
        $addressData->setStreet(ResourceApi::getResourcePropertyOrEmptyString($element, './street'));
        $addressData->setCity(ResourceApi::getResourcePropertyOrEmptyString($element, './locality'));
        $addressData->setPostalCode(ResourceApi::getResourcePropertyOrEmptyString($element, './pcode'));
        $addressData->setCountry(ResourceApi::getResourcePropertyOrEmptyString($element, './country'));

        return $addressData;
    }
}
