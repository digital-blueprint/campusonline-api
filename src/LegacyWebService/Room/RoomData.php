<?php

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

    // FIXME: schema.org/floorSize isn't a string
    /**
     * @var string
     */
    private $floorSize;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $permittedUsage;

    /**
     * @var string
     */
    private $alternateName;

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

    public function getFloorSize(): string
    {
        return $this->floorSize;
    }

    public function setFloorSize(string $floorSize): void
    {
        $this->floorSize = $floorSize;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getPermittedUsage(): string
    {
        return $this->permittedUsage;
    }

    public function setPermittedUsage(string $permittedUsage): void
    {
        $this->permittedUsage = $permittedUsage;
    }

    public function getAlternateName(): string
    {
        return $this->alternateName;
    }

    public function setAlternateName(string $alternateName): void
    {
        $this->alternateName = $alternateName;
    }
}