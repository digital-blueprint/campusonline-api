<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Course;

use Dbp\CampusonlineApi\LegacyWebService\ResourceData;

class CourseData extends ResourceData
{
    /** Search filters: Pass, if ANY of the given search filters passes or if NONE is given */
    /** @var string Partial, case-insensitive text search on the 'name' attribute. Passes if filter is empty. */
    public const NAME_SEARCH_FILTER_NAME = 'nameSearchFilter';

    public const NAME_ATTRIBUTE = 'name';
    public const LANGUAGE_ATTRIBUTE = 'language';
    public const CODE_ATTRIBUTE = 'code';
    public const TYPE_ATTRIBUTE = 'type';
    public const TYPE_NAME_ATTRIBUTE = 'typeName';
    public const TEACHING_TERM_ATTRIBUTE = 'teachingTerm';
    public const DESCRIPTION_ATTRIBUTE = 'description';
    public const NUMBER_OF_CREDITS_ATTRIBUTE = 'numberOfCredits';
    public const LEVEL_URL_ATTRIBUTE = 'levelUrl';
    public const ADMISSION_URL_ATTRIBUTE = 'admissionUrl';
    public const SYLLABUS_URL_ATTRIBUTE = 'syllabusUrl';
    public const EXAMS_URL_ATTRIBUTE = 'examsUrl';
    public const DATES_URL_ATTRIBUTE = 'datesUrl';
    public const CONTACTS_ATTRIBUTE = 'contacts';

    public function getName(): string
    {
        return $this->data[self::NAME_ATTRIBUTE];
    }

    public function setName(string $name): void
    {
        $this->data[self::NAME_ATTRIBUTE] = $name;
    }

    public function getLanguage(): string
    {
        return $this->data[self::LANGUAGE_ATTRIBUTE];
    }

    public function setLanguage(string $language): void
    {
        $this->data[self::LANGUAGE_ATTRIBUTE] = $language;
    }

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

    public function getTypeName(): string
    {
        return $this->data[self::TYPE_NAME_ATTRIBUTE];
    }

    public function setTypeName(string $typeName): void
    {
        $this->data[self::TYPE_NAME_ATTRIBUTE] = $typeName;
    }

    public function getTeachingTerm(): string
    {
        return $this->data[self::TEACHING_TERM_ATTRIBUTE];
    }

    public function setTeachingTerm(string $teachingTerm): void
    {
        $this->data[self::TEACHING_TERM_ATTRIBUTE] = $teachingTerm;
    }

    public function getDescription(): string
    {
        return $this->data[self::DESCRIPTION_ATTRIBUTE];
    }

    public function setDescription(string $description): void
    {
        $this->data[self::DESCRIPTION_ATTRIBUTE] = $description;
    }

    public function getNumberOfCredits(): float
    {
        return (float) $this->data[self::NUMBER_OF_CREDITS_ATTRIBUTE];
    }

    public function setNumberOfCredits(float $numberOfCredits): void
    {
        $this->data[self::NUMBER_OF_CREDITS_ATTRIBUTE] = $numberOfCredits;
    }

    public function getLevelUrl(): string
    {
        return $this->data[self::LEVEL_URL_ATTRIBUTE];
    }

    public function setLevelUrl(string $levelUrl): void
    {
        $this->data[self::LEVEL_URL_ATTRIBUTE] = $levelUrl;
    }

    public function getAdmissionUrl(): string
    {
        return $this->data[self::ADMISSION_URL_ATTRIBUTE];
    }

    public function setAdmissionUrl(string $admissionUrl): void
    {
        $this->data[self::ADMISSION_URL_ATTRIBUTE] = $admissionUrl;
    }

    public function getSyllabusUrl(): string
    {
        return $this->data[self::SYLLABUS_URL_ATTRIBUTE];
    }

    public function setSyllabusUrl(string $syllabusUrl): void
    {
        $this->data[self::SYLLABUS_URL_ATTRIBUTE] = $syllabusUrl;
    }

    public function getExamsUrl(): string
    {
        return $this->data[self::EXAMS_URL_ATTRIBUTE];
    }

    public function setExamsUrl(string $examsUrl): void
    {
        $this->data[self::EXAMS_URL_ATTRIBUTE] = $examsUrl;
    }

    public function getDatesUrl(): string
    {
        return $this->data[self::DATES_URL_ATTRIBUTE];
    }

    public function setDatesUrl(string $datesUrl): void
    {
        $this->data[self::DATES_URL_ATTRIBUTE] = $datesUrl;
    }

    public function getContacts(): array
    {
        return $this->data[self::CONTACTS_ATTRIBUTE];
    }

    public function setContacts(array $contacts): void
    {
        $this->data[self::CONTACTS_ATTRIBUTE] = $contacts;
    }
}
