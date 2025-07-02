<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Courses;

class CourseResource
{
    private const UID_ATTRIBUTE = 'uid';
    private const TITLE_ATTRIBUTE = 'code';
    private const TITLE_VALUE_ATTRIBUTE = 'value';
    private const COURSE_CODE_ATTRIBUTE = 'courseCode';
    private const SEMESTER_KEY_ATTRIBUTE = 'semesterKey';
    private const COURSE_TYPE_KEY_ATTRIBUTE = 'courseTypeKey';

    public function __construct(
        private readonly array $courseResourceData)
    {
    }

    public function getResourceData(): array
    {
        return $this->courseResourceData;
    }

    public function getUid(): ?string
    {
        return $this->courseResourceData[self::UID_ATTRIBUTE] ?? null;
    }

    public function getTitle(): ?array
    {
        return $this->courseResourceData[self::TITLE_ATTRIBUTE][self::TITLE_VALUE_ATTRIBUTE] ?? null;
    }

    public function getCourseCode(): ?string
    {
        return $this->courseResourceData[self::COURSE_CODE_ATTRIBUTE] ?? null;
    }

    public function getSemesterKey(): ?string
    {
        return $this->courseResourceData[self::SEMESTER_KEY_ATTRIBUTE] ?? null;
    }

    public function getCourseTypeKey(): ?string
    {
        return $this->courseResourceData[self::COURSE_TYPE_KEY_ATTRIBUTE] ?? null;
    }
}
