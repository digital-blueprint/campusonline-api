<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Room;

use Dbp\CampusonlineApi\LegacyWebService\ResourceData;

class RoomData extends ResourceData
{
    /** Search filters: Pass, if ANY of the given search filters passes or if NONE is given */

    /** @var string Partial, case-insensitive text search on the 'additionalInfo' attribute. Passes if filter is empty. */
    public const ADDITIONAL_INFO_SEARCH_FILTER_NAME = 'additionalInfoSearchFilter';

    /** @var string */
    public const ADDITIONAL_INFO_ATTRIBUTE_NAME = 'additionalInfo';

    /** @var string */
    private $identifier;

    /** @var string */
    private $name;

    /** @var string */
    private $address;

    /** @var string */
    private $url;

    /** @var float */
    private $floorSize;

    /** @var string */
    private $purpose;

    /** @var string */
    private $purposeId;

    /** @var string */
    private $additionalInfo;

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

    public function getPurposeId(): string
    {
        return $this->purposeId;
    }

    public function setPurposeId(string $purposeId): void
    {
        $this->purposeId = $purposeId;
    }

    public function getPurpose(): string
    {
        return $this->purpose;
    }

    public function setPurpose(string $purpose): void
    {
        $this->purpose = $purpose;
    }

    public function getAdditionalInfo(): string
    {
        return $this->additionalInfo;
    }

    public function setAdditionalInfo(string $additionalInfo): void
    {
        $this->additionalInfo = $additionalInfo;
    }
}
