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
    private $type;

    /**
     * @var string
     */
    private $language;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $teachingTerm;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $numberOfCredits;

    /**
     * @var PersonData[]
     */
    private $contacts;

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $courseCode): void
    {
        $this->code = $courseCode;
    }

    public function getTpe(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getTeachingTerm(): string
    {
        return $this->teachingTerm;
    }

    public function setTeachingTerm(string $teachingTerm): void
    {
        $this->teachingTerm = $teachingTerm;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getNumberOfCredits(): string
    {
        return $this->numberOfCredits;
    }

    public function setNumberOfCredits(string $numberOfCredits): void
    {
        $this->numberOfCredits = $numberOfCredits;
    }

    public function getContacts(): array
    {
        return $this->contacts;
    }

    public function setContacts(array $contacts): void
    {
        $this->contacts = $contacts;
    }
}
