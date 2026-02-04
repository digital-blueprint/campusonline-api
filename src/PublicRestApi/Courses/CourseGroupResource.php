<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Courses;

use Dbp\CampusonlineApi\PublicRestApi\Resource;

class CourseGroupResource extends Resource
{
    private const COURSE_UID_ATTRIBUTE = 'courseUid';
    private const EVENTS_ATTRIBUTE = 'events';
    private const EVENT_UID_ATTRIBUTE = 'uid';
    private const EVENT_START_ATTRIBUTE = 'start';
    private const EVENT_END_ATTRIBUTE = 'end';
    private const EVENT_STATUS_TYPE_ATTRIBUTE = 'statusType';
    private const EVENT_TYPE_ATTRIBUTE = 'eventType';

    public function getUid(): ?string
    {
        return $this->resourceData[self::UID_ATTRIBUTE] ?? null;
    }

    public function getCourseUid(): ?string
    {
        return $this->resourceData[self::COURSE_UID_ATTRIBUTE] ?? null;
    }

    public function getName(string $languageTag = self::DEFAULT_LANGUAGE_TAG): ?string
    {
        return $this->resourceData[self::NAME_ATTRIBUTE][self::VALUE_ATTRIBUTE][$languageTag] ?? null;
    }

    public function getNumberOfEvents(): int
    {
        return count($this->resourceData[self::EVENTS_ATTRIBUTE][self::ITEMS_ATTRIBUTE] ?? []);
    }

    public function getEventUid(int $eventIndex): ?string
    {
        return $this->getEvent($eventIndex)[self::EVENT_UID_ATTRIBUTE] ?? null;
    }

    public function getEventStart(int $eventIndex): ?string
    {
        return $this->getEvent($eventIndex)[self::EVENT_START_ATTRIBUTE] ?? null;
    }

    public function getEventEnd(int $eventIndex): ?string
    {
        return $this->getEvent($eventIndex)[self::EVENT_END_ATTRIBUTE] ?? null;
    }

    public function getEventStatusType(int $eventIndex, string $languageTag = self::DEFAULT_LANGUAGE_TAG): ?string
    {
        return $this->getEvent($eventIndex)[self::EVENT_STATUS_TYPE_ATTRIBUTE][self::VALUE_ATTRIBUTE][$languageTag] ?? null;
    }

    public function getEventEventType(int $eventIndex, string $languageTag = self::DEFAULT_LANGUAGE_TAG): ?string
    {
        return $this->getEvent($eventIndex)[self::EVENT_TYPE_ATTRIBUTE][self::VALUE_ATTRIBUTE][$languageTag] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getEvent(int $eventIndex): array
    {
        return $this->resourceData[self::EVENTS_ATTRIBUTE][self::ITEMS_ATTRIBUTE][$eventIndex];
    }
}
