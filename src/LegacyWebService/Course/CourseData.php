<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Course;

use Dbp\CampusonlineApi\LegacyWebService\Person\PersonData;

class CourseData
{
    /** @var string */
    private $identifier;

    /** @var string */
    private $name;

    /** @var string */
    private $type;

    /** @var string */
    private $typeName;

    /** @var string */
    private $language;

    /** @var string */
    private $code;

    /** @var string */
    private $teachingTerm;

    /** @var string */
    private $description;

    /** @var float */
    private $numberOfCredits;

    /** @var string */
    private $levelUrl;

    /** @var string */
    private $admissionUrl;

    /** @var string */
    private $syllabusUrl;

    /** @var string */
    private $examsUrl;

    /** @var string */
    private $datesUrl;

    /** @var PersonData[] */
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

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getTypeName(): string
    {
        return $this->typeName;
    }

    public function setTypeName(string $typeName): void
    {
        $this->typeName = $typeName;
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

    public function getNumberOfCredits(): float
    {
        return $this->numberOfCredits;
    }

    public function setNumberOfCredits(float $numberOfCredits): void
    {
        $this->numberOfCredits = $numberOfCredits;
    }

    public function getLevelUrl(): string
    {
        return $this->levelUrl;
    }

    public function setLevelUrl(string $levelUrl): void
    {
        $this->levelUrl = $levelUrl;
    }

    public function getAdmissionUrl(): string
    {
        return $this->admissionUrl;
    }

    public function setAdmissionUrl(string $admissionUrl): void
    {
        $this->admissionUrl = $admissionUrl;
    }

    public function getSyllabusUrl(): string
    {
        return $this->syllabusUrl;
    }

    public function setSyllabusUrl(string $syllabusUrl): void
    {
        $this->syllabusUrl = $syllabusUrl;
    }

    public function getExamsUrl(): string
    {
        return $this->examsUrl;
    }

    public function setExamsUrl(string $examsUrl): void
    {
        $this->examsUrl = $examsUrl;
    }

    public function getDatesUrl(): string
    {
        return $this->datesUrl;
    }

    public function setDatesUrl(string $datesUrl): void
    {
        $this->datesUrl = $datesUrl;
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
