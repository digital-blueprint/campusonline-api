<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Room;

class RoomData
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $address;

    /**
     * @var string
     */
    private $url;

    /**
     * @var float
     */
    private $floorSize;

    /**
     * @var int
     */
    private $purposeID;

    /**
     * @var string
     */
    private $roomCode;

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getFloorSize(): float
    {
        return $this->floorSize;
    }

    public function setFloorSize(float $floorSize): void
    {
        $this->floorSize = $floorSize;
    }

    public function getPurposeID(): int
    {
        return $this->purposeID;
    }

    public function setPurposeID(int $permittedUsage): void
    {
        $this->purposeID = $permittedUsage;
    }

    public function getRoomCode(): string
    {
        return $this->roomCode;
    }

    public function setRoomCode(string $alternateName): void
    {
        $this->roomCode = $alternateName;
    }
}
