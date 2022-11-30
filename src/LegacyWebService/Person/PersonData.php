<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Person;

use Dbp\CampusonlineApi\LegacyWebService\ResourceData;

class PersonData
{
    public const GIVEN_NAME_ATTRIBUTE = 'givenName';
    public const FAMILY_NAME_ATTRIBUTE = 'familyName';
    public const EMAIL_ATTRIBUTE = 'email';

    /** @var array */
    private $data;

    public function __construct()
    {
        $this->data = [];
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->data[ResourceData::IDENTIFIER_ATTRIBUTE] = $identifier;
    }

    public function getIdentifier(): string
    {
        return $this->data[ResourceData::IDENTIFIER_ATTRIBUTE];
    }

    public function getGivenName(): string
    {
        return $this->data[self::GIVEN_NAME_ATTRIBUTE];
    }

    public function setGivenName(string $givenName): void
    {
        $this->data[self::GIVEN_NAME_ATTRIBUTE] = $givenName;
    }

    public function getFamilyName(): string
    {
        return $this->data[self::FAMILY_NAME_ATTRIBUTE];
    }

    public function setFamilyName(string $familyName): void
    {
        $this->data[self::FAMILY_NAME_ATTRIBUTE] = $familyName;
    }

    public function getEmail(): string
    {
        return $this->data[self::EMAIL_ATTRIBUTE];
    }

    public function setEmail(string $email): void
    {
        $this->data[self::EMAIL_ATTRIBUTE] = $email;
    }
}
