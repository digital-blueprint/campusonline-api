<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Person;

class PersonData
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $givenName;

    /**
     * @var string
     */
    private $familyName;

    /**
     * @var string
     */
    private $email;

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function setGivenName(string $givenName): void
    {
        $this->givenName = $givenName;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    public function setFamilyName(string $familyName): void
    {
        $this->familyName = $familyName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
}
