<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Courses;

use Dbp\CampusonlineApi\PublicRestApi\Resource;

class CourseTypeResource extends Resource
{
    private const KEY_ATTRIBUTE = 'key';
    private const NAME_ATTRIBUTE = 'name';

    public function getUid(): ?string
    {
        return $this->resourceData[self::UID_ATTRIBUTE] ?? null;
    }

    public function getKey(): ?string
    {
        return $this->resourceData[self::KEY_ATTRIBUTE] ?? null;
    }

    public function getName(string $languageTag = self::DEFAULT_LANGUAGE_TAG): ?array
    {
        return $this->resourceData[self::NAME_ATTRIBUTE][self::VALUE_ATTRIBUTE][$languageTag] ?? null;
    }
}
