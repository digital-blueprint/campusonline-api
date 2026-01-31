<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Persons;

use Dbp\CampusonlineApi\PublicRestApi\Resource;

class PersonClaimsResource extends Resource
{
    private const GIVEN_NAME_ATTRIBUTE = 'givenName';
    private const SURNAME_ATTRIBUTE = 'surname';
    private const EMAIL_ATTRIBUTE = 'email';
    private const MATRICULATION_NUMBER_ATTRIBUTE = 'matriculationNumber';
    private const DATE_OF_BIRTH_ATTRIBUTE = 'dateOfBirth';
    private const TITLE_PREFIX_ATTRIBUTE = 'titlePrefix';
    private const TITLE_SUFFIX_ATTRIBUTE = 'titleSuffix';
    private const GENDER_KEY_ATTRIBUTE = 'genderKey';

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
}
