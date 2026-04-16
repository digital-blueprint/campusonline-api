<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Courses;

use Dbp\CampusonlineApi\PublicRestApi\Resource;

class CourseDescriptionResource extends Resource
{
    public const UID_ATTRIBUTE = 'uid';
    public const CONTENT_ATTRIBUTE = 'content';
    public const OBJECTIVE_ATTRIBUTE = 'objective';
    public const TEACHING_METHOD_KEY_ATTRIBUTE = 'teachingMethodKey';
    public const TEACHING_METHOD_DESCRIPTION_ATTRIBUTE = 'teachingMethodDescription';
    public const EXPECTED_PREVIOUS_KNOWLEDGE_ATTRIBUTE = 'expectedPreviousKnowledge';

    public function getUid(): string
    {
        return $this->resourceData[self::UID_ATTRIBUTE];
    }

    public function getContent(): ?string
    {
        return $this->resourceData[self::CONTENT_ATTRIBUTE][self::VALUE_ATTRIBUTE] ?? null;
    }

    public function getContentLocalized(string $languageTag = self::DEFAULT_LANGUAGE_TAG): ?string
    {
        return $this->resourceData[self::CONTENT_ATTRIBUTE][self::VALUE_ATTRIBUTE][$languageTag] ?? null;
    }

    public function getObjective(): ?string
    {
        return $this->resourceData[self::OBJECTIVE_ATTRIBUTE][self::VALUE_ATTRIBUTE] ?? null;
    }

    public function getObjectiveLocalized(string $languageTag = self::DEFAULT_LANGUAGE_TAG): ?string
    {
        return $this->resourceData[self::OBJECTIVE_ATTRIBUTE][self::VALUE_ATTRIBUTE][$languageTag] ?? null;
    }

    public function getTeachingMethodKey(): ?string
    {
        return $this->resourceData[self::TEACHING_METHOD_KEY_ATTRIBUTE] ?? null;
    }

    public function getTeachingMethodDescription(): ?string
    {
        return $this->resourceData[self::TEACHING_METHOD_DESCRIPTION_ATTRIBUTE][self::VALUE_ATTRIBUTE] ?? null;
    }

    public function getTeachingMethodDescriptionLocalized(string $languageTag = self::DEFAULT_LANGUAGE_TAG): ?string
    {
        return $this->resourceData[self::TEACHING_METHOD_DESCRIPTION_ATTRIBUTE][self::VALUE_ATTRIBUTE][$languageTag] ?? null;
    }

    public function getExpectedPreviousKnowledge(): ?string
    {
        return $this->resourceData[self::EXPECTED_PREVIOUS_KNOWLEDGE_ATTRIBUTE][self::VALUE_ATTRIBUTE] ?? null;
    }

    public function getExpectedPreviousKnowledgeLocalized(string $languageTag = self::DEFAULT_LANGUAGE_TAG): ?string
    {
        return $this->resourceData[self::EXPECTED_PREVIOUS_KNOWLEDGE_ATTRIBUTE][self::VALUE_ATTRIBUTE][$languageTag] ?? null;
    }
}
