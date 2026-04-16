<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Appointments;

use Dbp\CampusonlineApi\PublicRestApi\Resource;

class AppointmentResource extends Resource
{
    public const REGULAR_CLASS_EVENT_TYPE_KEY = 'REGULAR';
    public const EXAM_EVENT_TYPE_KEY = 'EXAM_DATE';

    private const COURSE_UID_ATTRIBUTE = 'courseUid';
    private const START_AT_ATTRIBUTE = 'startAt';
    private const END_AT_ATTRIBUTE = 'endAt';
    private const COURSE_GROUP_UID_ATTRIBUTE = 'courseGroupUid';
    private const ROOM_UID_ATTRIBUTE = 'roomUid';
    private const STATUS_TYPE_KEY_ATTRIBUTE = 'statusTypeKey';
    private const EVENT_TYPE_KEY_ATTRIBUTE = 'eventTypeKey';
    private const COMMENT_ATTRIBUTE = 'comment';
    private const APPLICATION_TYPE_KEY_ATTRIBUTE = 'applicationTypeKey';
    private const RESOURCE_URL_ATTRIBUTE = 'resourceUrl';
    private const EXTERNAL_OBJECT_UID_ATTRIBUTE = 'externalObjectUid';
    private const RESOURCE_UID_ATTRIBUTE = 'resourceUId';

    public function getUid(): ?string
    {
        return $this->resourceData[self::UID_ATTRIBUTE] ?? null;
    }

    public function getCourseUid(): ?string
    {
        return $this->resourceData[self::COURSE_UID_ATTRIBUTE] ?? null;
    }

    public function getStartAt(): ?string
    {
        return $this->resourceData[self::START_AT_ATTRIBUTE] ?? null;
    }

    public function getEndAt(): ?string
    {
        return $this->resourceData[self::END_AT_ATTRIBUTE] ?? null;
    }

    public function getCourseGroupUid(): ?string
    {
        return $this->resourceData[self::COURSE_GROUP_UID_ATTRIBUTE] ?? null;
    }

    public function getRoomUid(): ?string
    {
        return $this->resourceData[self::ROOM_UID_ATTRIBUTE] ?? null;
    }

    public function getStatusTypeKey(): ?string
    {
        return $this->resourceData[self::STATUS_TYPE_KEY_ATTRIBUTE] ?? null;
    }

    public function getEventTypeKey(): ?string
    {
        return $this->resourceData[self::EVENT_TYPE_KEY_ATTRIBUTE] ?? null;
    }

    public function getComment(): ?string
    {
        return $this->resourceData[self::COMMENT_ATTRIBUTE] ?? null;
    }

    public function getApplicationTypeKey(): ?string
    {
        return $this->resourceData[self::APPLICATION_TYPE_KEY_ATTRIBUTE] ?? null;
    }

    public function getResourceUrl(): ?string
    {
        return $this->resourceData[self::RESOURCE_URL_ATTRIBUTE] ?? null;
    }

    public function getExternalObjectUid(): ?string
    {
        return $this->resourceData[self::EXTERNAL_OBJECT_UID_ATTRIBUTE] ?? null;
    }

    public function getResourceUid(): ?string
    {
        return $this->resourceData[self::RESOURCE_UID_ATTRIBUTE] ?? null;
    }
}
