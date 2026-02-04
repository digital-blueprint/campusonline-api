<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Persons;

use Dbp\CampusonlineApi\PublicRestApi\Resource;

class PersonClaimsResource extends Resource
{
    private const GIVEN_NAME_ATTRIBUTE = 'givenName';
    private const SURNAME_ATTRIBUTE = 'surname';
    private const EMAIL_ATTRIBUTE = 'email';
    private const EMAIL_EMPLOYEE_ATTRIBUTE = 'emailEmployee';
    private const EMAIL_STUDENT_ATTRIBUTE = 'emailStudent';
    private const EMAIL_EXTPERS_ATTRIBUTE = 'emailExtpers';
    private const MATRICULATION_NUMBER_ATTRIBUTE = 'matriculationNumber';
    private const DATE_OF_BIRTH_ATTRIBUTE = 'dateOfBirth';
    private const TITLE_PREFIX_ATTRIBUTE = 'titlePrefix';
    private const TITLE_SUFFIX_ATTRIBUTE = 'titleSuffix';
    private const GENDER_KEY_ATTRIBUTE = 'genderKey';
    private const PERSON_GROUPS_ATTRIBUTE = 'personGroups';
    private const ADDRESSES_ATTRIBUTE = 'addresses';
    private const COUNTRY_ATTRIBUTE = 'country';
    private const CITY_ATTRIBUTE = 'city';
    private const POSTAL_CODE_ATTRIBUTE = 'postalCode';
    private const STREET_ATTRIBUTE = 'street';
    private const EMPLOYEE_ADDRESS_TYPE_ABBREVIATION_ATTRIBUTE = 'employeeAddressTypeAbbreviation';
    private const BUSINESS_CARD_URL_EMPLOYEE_ATTRIBUTE = 'businessCardUrlEmployee';
    private const ADDITIONAL_ADDRESS_INFO_ATTRIBUTE = 'additionalAddressInfo';
    private const MOBILE_PHONE_NUMBER_EMPLOYEE_ATTRIBUTE = 'mobilePhoneNumberEmployee';
    private const EXTERNAL_PHONE_NUMBER_EMPLOYEE_ATTRIBUTE = 'externalPhoneNumberEmployee';
    private const INTERNAL_PHONE_NUMBERS_EMPLOYEE_ATTRIBUTE = 'internalPhoneNumbersEmployee';

    public function getUid(): ?string
    {
        return $this->resourceData['uid'] ?? null;
    }

    public function getGivenName(): ?string
    {
        return $this->resourceData[self::GIVEN_NAME_ATTRIBUTE] ?? null;
    }

    public function getSurname(): ?string
    {
        return $this->resourceData[self::SURNAME_ATTRIBUTE] ?? null;
    }

    public function getEmail(): ?string
    {
        return $this->resourceData[self::EMAIL_ATTRIBUTE] ?? null;
    }

    public function getEmailEmployee(): ?string
    {
        return $this->resourceData[self::EMAIL_EMPLOYEE_ATTRIBUTE] ?? null;
    }

    public function getEmailStudent(): ?string
    {
        return $this->resourceData[self::EMAIL_STUDENT_ATTRIBUTE] ?? null;
    }

    public function getEmailExtpers(): ?string
    {
        return $this->resourceData[self::EMAIL_EXTPERS_ATTRIBUTE] ?? null;
    }

    public function getMatriculationNumber(): ?string
    {
        return $this->resourceData[self::MATRICULATION_NUMBER_ATTRIBUTE] ?? null;
    }

    public function getDateOfBirth(): ?string
    {
        return $this->resourceData[self::DATE_OF_BIRTH_ATTRIBUTE] ?? null;
    }

    public function getTitlePrefix(): ?string
    {
        return $this->resourceData[self::TITLE_PREFIX_ATTRIBUTE] ?? null;
    }

    public function getTitleSuffix(): ?string
    {
        return $this->resourceData[self::TITLE_SUFFIX_ATTRIBUTE] ?? null;
    }

    public function getGenderKey(): ?string
    {
        return $this->resourceData[self::GENDER_KEY_ATTRIBUTE] ?? null;
    }

    public function getPersonGroups(): ?array
    {
        return $this->resourceData[self::PERSON_GROUPS_ATTRIBUTE] ?? null;
    }

    public function getNumAddresses(): int
    {
        return count($this->resourceData[self::ADDRESSES_ATTRIBUTE] ?? []);
    }

    public function getAddressCountry(int $addressIndex): ?string
    {
        return $this->resourceData[self::ADDRESSES_ATTRIBUTE][$addressIndex][self::COUNTRY_ATTRIBUTE] ?? null;
    }

    public function getAddressCity(int $addressIndex): ?string
    {
        return $this->resourceData[self::ADDRESSES_ATTRIBUTE][$addressIndex][self::CITY_ATTRIBUTE] ?? null;
    }

    public function getAddressPostalCode(int $addressIndex): ?string
    {
        return $this->resourceData[self::ADDRESSES_ATTRIBUTE][$addressIndex][self::POSTAL_CODE_ATTRIBUTE] ?? null;
    }

    public function getAddressStreet(int $addressIndex): ?string
    {
        return $this->resourceData[self::ADDRESSES_ATTRIBUTE][$addressIndex][self::STREET_ATTRIBUTE] ?? null;
    }

    public function getEmployeeAddressTypeAbbreviation(int $addressIndex): ?string
    {
        return $this->resourceData[self::ADDRESSES_ATTRIBUTE][$addressIndex][self::EMPLOYEE_ADDRESS_TYPE_ABBREVIATION_ATTRIBUTE] ?? null;
    }

    public function getAdditionalAddressInfo(int $addressIndex): ?string
    {
        return $this->resourceData[self::ADDRESSES_ATTRIBUTE][$addressIndex][self::ADDITIONAL_ADDRESS_INFO_ATTRIBUTE] ?? null;
    }

    public function getBusinessCardUrlEmployee(): ?string
    {
        return $this->resourceData[self::BUSINESS_CARD_URL_EMPLOYEE_ATTRIBUTE] ?? null;
    }

    public function getMobilePhoneNumberEmployee(): ?string
    {
        return $this->resourceData[self::MOBILE_PHONE_NUMBER_EMPLOYEE_ATTRIBUTE] ?? null;
    }

    public function getExternalPhoneNumberEmployee(): ?string
    {
        return $this->resourceData[self::EXTERNAL_PHONE_NUMBER_EMPLOYEE_ATTRIBUTE] ?? null;
    }

    public function getInternalPhoneNumbersEmployee(): ?array
    {
        return $this->resourceData[self::INTERNAL_PHONE_NUMBERS_EMPLOYEE_ATTRIBUTE] ?? null;
    }
}
