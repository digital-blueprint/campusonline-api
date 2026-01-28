<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Courses;

use Dbp\CampusonlineApi\PublicRestApi\Resource;

class CourseResource extends Resource
{
    private const TITLE_ATTRIBUTE = 'title';
    private const TITLE_VALUE_ATTRIBUTE = 'value';
    private const COURSE_CODE_ATTRIBUTE = 'courseCode';
    private const SEMESTER_KEY_ATTRIBUTE = 'semesterKey';
    private const COURSE_TYPE_KEY_ATTRIBUTE = 'courseTypeKey';
    private const COURSE_IDENTITY_CODE_UID_ATTRIBUTE = 'courseIdentityCodeUid';
    private const ORGANISATION_UID_ATTRIBUTE = 'organisationUid';
    private const SEMESTER_HOURS_ATTRIBUTE = 'semesterHours';
    private const MAIN_LANGUAGE_OF_INSTRUCTION_ATTRIBUTE = 'mainLanguageOfInstruction';

    public function getUid(): ?string
    {
        return $this->resourceData[self::UID_ATTRIBUTE] ?? null;
    }

    public function getTitle(): ?array
    {
        return $this->resourceData[self::TITLE_ATTRIBUTE][self::TITLE_VALUE_ATTRIBUTE] ?? null;
    }

    public function getCourseCode(): ?string
    {
        return $this->resourceData[self::COURSE_CODE_ATTRIBUTE] ?? null;
    }

    public function getSemesterKey(): ?string
    {
        return $this->resourceData[self::SEMESTER_KEY_ATTRIBUTE] ?? null;
    }

    public function getCourseTypeKey(): ?string
    {
        return $this->resourceData[self::COURSE_TYPE_KEY_ATTRIBUTE] ?? null;
    }

    public function getCourseIdentityCodeUid(): ?string
    {
        return $this->resourceData[self::COURSE_IDENTITY_CODE_UID_ATTRIBUTE] ?? null;
    }

    public function getOrganisationUid(): ?string
    {
        return $this->resourceData[self::ORGANISATION_UID_ATTRIBUTE] ?? null;
    }

    public function getSemesterHours(): ?float
    {
        return $this->resourceData[self::SEMESTER_HOURS_ATTRIBUTE] ?? null;
    }

    public function getMainLanguageOfInstruction(): ?string
    {
        return $this->resourceData[self::MAIN_LANGUAGE_OF_INSTRUCTION_ATTRIBUTE] ?? null;
    }
}
