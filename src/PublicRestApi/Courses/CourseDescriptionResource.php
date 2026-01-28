<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Courses;

use Dbp\CampusonlineApi\PublicRestApi\Resource;

class CourseDescriptionResource extends Resource
{
    public const UID_ATTRIBUTE = 'uid';
    public const CONTENT_ATTRIBUTE = 'content';
    public const OBJECTIVE_ATTRIBUTE = 'objective';

    public function getUid(): string
    {
        return $this->resourceData[self::UID_ATTRIBUTE];
    }

    public function getContent(string $languageTag = self::DEFAULT_LANGUAGE_TAG): ?array
    {
        return $this->resourceData[self::CONTENT_ATTRIBUTE][self::VALUE_ATTRIBUTE][$languageTag] ?? null;
    }

    public function getObjective(string $languageTag = self::DEFAULT_LANGUAGE_TAG): ?array
    {
        return $this->resourceData[self::OBJECTIVE_ATTRIBUTE][self::VALUE_ATTRIBUTE][$languageTag] ?? null;
    }
}
