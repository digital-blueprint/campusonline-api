<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Course;

use Dbp\CampusonlineApi\LegacyWebService\Person\PersonData;

class CourseData
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $learningResourceType;

    /**
     * @var string
     */
    private $language;

    /**
     * @var string
     */
    private $educationalLevel;

    /**
     * @var string
     */
    private $courseCode;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $citation;

    /**
     * @var string
     */
    private $numberOfCredits;

    /**
     * @var string
     */
    private $occupationalCredentialAwarded;

    /**
     * @var string
     */
    private $availableLanguage;

    /**
     * @var string
     */
    private $url;

    /**
     * @var PersonData[]
     */
    private $maintainer;

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getEducationalLevel(): string
    {
        return $this->educationalLevel;
    }

    public function getCourseCode(): string
    {
        return $this->courseCode;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCitation(): string
    {
        return $this->citation;
    }

    public function getNumberOfCredits(): string
    {
        return $this->numberOfCredits;
    }

    public function getOccupationalCredentialAwarded(): string
    {
        return $this->occupationalCredentialAwarded;
    }

    public function getAvailableLanguage(): string
    {
        return $this->availableLanguage;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMaintainer(): array
    {
        return $this->maintainer;
    }

    public function getLearningResourceType(): ?string
    {
        return $this->learningResourceType;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    public function setEducationalLevel(string $educationalLevel): void
    {
        $this->educationalLevel = $educationalLevel;
    }

    public function setCourseCode(string $courseCode): void
    {
        $this->courseCode = $courseCode;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setCitation(string $citation): void
    {
        $this->citation = $citation;
    }

    public function setNumberOfCredits(string $numberOfCredits): void
    {
        $this->numberOfCredits = $numberOfCredits;
    }

    public function setOccupationalCredentialAwarded(string $occupationalCredentialAwarded): void
    {
        $this->occupationalCredentialAwarded = $occupationalCredentialAwarded;
    }

    public function setAvailableLanguage(string $availableLanguage): void
    {
        $this->availableLanguage = $availableLanguage;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setMaintainer(array $maintainer): void
    {
        $this->maintainer = $maintainer;
    }

    public function setLearningResourceType(string $learningResourceType): void
    {
        $this->learningResourceType = $learningResourceType;
    }
}
