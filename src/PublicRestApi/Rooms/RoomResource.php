<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Rooms;

class RoomResource
{
    public const ROOM_UID_ATTRIBUTE = 'roomUid';
    public const KEY_ATTRIBUTE = 'key';
    private const ADDRESS_ATTRIBUTE = 'address';
    private const ROOM_ID_ARCHITECT_ATTRIBUTE = 'roomIdArchitect';
    private const ROOM_ADDITIONAL_INFO_ATTRIBUTE = 'roomAdditionalInfo';
    private const ROOM_ORGANISATIONS_ATTRIBUTE = 'roomOrganisations';
    private const ORG_ROOM_UID_ATTRIBUTE = 'orgRoomUid';

    public function __construct(
        private readonly array $roomResourceData)
    {
    }

    public function getResourceData(): array
    {
        return $this->roomResourceData;
    }

    public function getRoomUid(): ?int
    {
        return $this->roomResourceData[self::ROOM_UID_ATTRIBUTE] ?? null;
    }

    public function getKey(): ?string
    {
        return $this->roomResourceData[self::KEY_ATTRIBUTE] ?? null;
    }

    public function getAddress(): ?string
    {
        return $this->roomResourceData[self::ADDRESS_ATTRIBUTE] ?? null;
    }

    public function getRoomIdArchitect(): ?string
    {
        return $this->roomResourceData[self::ROOM_ID_ARCHITECT_ATTRIBUTE] ?? null;
    }

    public function getRoomAdditionalInfo(): ?string
    {
        return $this->roomResourceData[self::ROOM_ADDITIONAL_INFO_ATTRIBUTE] ?? null;
    }

    public function getRoomOrganizations(): iterable
    {
        foreach ($this->roomResourceData[self::ROOM_ORGANISATIONS_ATTRIBUTE] ?? [] as $roomOrganization) {
            yield $roomOrganization[self::ORG_ROOM_UID_ATTRIBUTE];
        }
    }
}
