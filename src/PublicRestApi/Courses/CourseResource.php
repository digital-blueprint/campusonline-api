<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Courses;

class CourseResource
{
    private const UID_ATTRIBUTE = 'uid';
    private const TITLE_ATTRIBUTE = 'code';
    private const COURSE_CODE_ATTRIBUTE = 'courseCode';
    private const SEMESTER_KEY_ATTRIBUTE = 'semesterKey';
    private const COURSE_TYPE_KEY_ATTRIBUTE = 'courseTypeKey';

    public function __construct(
        private readonly array $courseData)
    {
    }

    public function getUid(): ?string
    {
        return $this->courseData[self::UID_ATTRIBUTE] ?? null;
    }

    public function getTitle(): ?array
    {
        return $this->courseData[self::TITLE_ATTRIBUTE] ?? null;
    }

    public function getCourseCode(): ?string
    {
        return $this->courseData[self::COURSE_CODE_ATTRIBUTE] ?? null;
    }

    public function getSemesterKey(): ?string
    {
        return $this->courseData[self::SEMESTER_KEY_ATTRIBUTE] ?? null;
    }

    public function getCourseTypeKey(): ?string
    {
        return $this->courseData[self::COURSE_TYPE_KEY_ATTRIBUTE] ?? null;
    }
}
