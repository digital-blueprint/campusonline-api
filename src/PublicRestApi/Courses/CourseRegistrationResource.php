<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Courses;

use Dbp\CampusonlineApi\PublicRestApi\Resource;

class CourseRegistrationResource extends Resource
{
    private const UID_ATTRIBUTE = 'uid';
    private const PERSON_UID_ATTRIBUTE = 'personUid';
    private const COURSE_UID_ATTRIBUTE = 'courseUid';

    public function getUid(): ?string
    {
        return $this->resourceData[self::UID_ATTRIBUTE] ?? null;
    }

    public function getCourseUid(): ?string
    {
        return $this->resourceData[self::COURSE_UID_ATTRIBUTE] ?? null;
    }

    public function getPersonUid(): ?string
    {
        return $this->resourceData[self::PERSON_UID_ATTRIBUTE] ?? null;
    }
}
