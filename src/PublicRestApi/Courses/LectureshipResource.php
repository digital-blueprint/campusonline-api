<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Courses;

use Dbp\CampusonlineApi\PublicRestApi\Resource;

class LectureshipResource extends Resource
{
    private const UID_ATTRIBUTE = 'uid';
    private const FUNCTION_KEY_ATTRIBUTE = 'functionKey';
    private const PERSON_UID_ATTRIBUTE = 'personUid';
    private const COURSE_UID_ATTRIBUTE = 'courseUid';
    private const GROUPS_ATTRIBUTE = 'groups';
    private const GROUP_UID_ATTRIBUTE = 'groupUid';

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

    public function getFunctionKey(): ?string
    {
        return $this->resourceData[self::FUNCTION_KEY_ATTRIBUTE] ?? null;
    }

    /**
     * @return array<int, string>
     */
    public function getGroupUids(): array
    {
        return array_filter($this->resourceData[self::GROUPS_ATTRIBUTE][self::ITEMS_ATTRIBUTE] ?? [],
            fn (array $group) => $group[self::GROUP_UID_ATTRIBUTE]
        );
    }
}
