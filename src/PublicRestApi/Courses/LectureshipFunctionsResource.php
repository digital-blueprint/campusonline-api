<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Courses;

use Dbp\CampusonlineApi\PublicRestApi\Resource;

class LectureshipFunctionsResource extends Resource
{
    public function getKey(): ?string
    {
        return $this->resourceData[self::KEY_ATTRIBUTE] ?? null;
    }

    public function getLocalizedName(string $languageTag = self::DEFAULT_LANGUAGE_TAG): ?string
    {
        return $this->resourceData[self::NAME_ATTRIBUTE][self::VALUE_ATTRIBUTE][$languageTag] ?? null;
    }

    public function getName(): ?array
    {
        return $this->resourceData[self::NAME_ATTRIBUTE][self::VALUE_ATTRIBUTE] ?? null;
    }
}
