<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Courses;

use Dbp\CampusonlineApi\PublicRestApi\Resource;

class CourseResource extends Resource
{
    private const UID_ATTRIBUTE = 'uid';
    private const TITLE_ATTRIBUTE = 'title';
    private const TITLE_VALUE_ATTRIBUTE = 'value';
    private const COURSE_CODE_ATTRIBUTE = 'courseCode';
    private const SEMESTER_KEY_ATTRIBUTE = 'semesterKey';
    private const COURSE_TYPE_KEY_ATTRIBUTE = 'courseTypeKey';

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
}
