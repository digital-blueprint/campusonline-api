<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Person;

use Dbp\CampusonlineApi\LegacyWebService\ResourceData;

class PersonData extends ResourceData
{
    public const GIVEN_NAME_ATTRIBUTE = 'givenName';
    public const FAMILY_NAME_ATTRIBUTE = 'familyName';
    public const EMAIL_ATTRIBUTE = 'email';
    public const IDENT_ATTRIBUTE = 'ident';

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

    public function getIdent(): string
    {
        return $this->data[self::IDENT_ATTRIBUTE];
    }

    public function setIdent(string $email): void
    {
        $this->data[self::IDENT_ATTRIBUTE] = $email;
    }
}
