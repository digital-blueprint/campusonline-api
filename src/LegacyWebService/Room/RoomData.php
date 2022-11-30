<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Room;

use Dbp\CampusonlineApi\LegacyWebService\ResourceData;

class RoomData extends ResourceData
{
    /** Search filters: Pass, if ANY of the given search filters passes or if NONE is given */
    /** @var string Partial, case-insensitive text search on the 'additionalInfo' attribute. Passes if filter is empty. */
    public const ADDITIONAL_INFO_SEARCH_FILTER_NAME = 'additionalInfoSearchFilter';

    public const ADDRESS_ATTRIBUTE = 'address';
    public const URL_ATTRIBUTE = 'url';
    public const FLOOR_SIZE_ATTRIBUTE = 'floorSize';
    public const PURPOSE_ATTRIBUTE = 'purpose';
    public const PURPOSE_ID_ATTRIBUTE = 'purposeId';
    public const ADDITIONAL_INFO_ATTRIBUTE = 'additionalInfo';

    public function getAddress(): string
    {
        return $this->data[self::ADDRESS_ATTRIBUTE];
    }

    public function setAddress(string $address): void
    {
        $this->data[self::ADDRESS_ATTRIBUTE] = $address;
    }

    public function getUrl(): string
    {
        return $this->data[self::URL_ATTRIBUTE];
    }

    public function setUrl(string $url): void
    {
        $this->data[self::URL_ATTRIBUTE] = $url;
    }

    public function getFloorSize(): float
    {
        return $this->data[self::FLOOR_SIZE_ATTRIBUTE];
    }

    public function setFloorSize(float $floorSize): void
    {
        $this->data[self::FLOOR_SIZE_ATTRIBUTE] = $floorSize;
    }

    public function getPurposeId(): string
    {
        return $this->data[self::PURPOSE_ID_ATTRIBUTE];
    }

    public function setPurposeId(string $purposeId): void
    {
        $this->data[self::PURPOSE_ID_ATTRIBUTE] = $purposeId;
    }

    public function getPurpose(): string
    {
        return $this->data[self::PURPOSE_ATTRIBUTE];
    }

    public function setPurpose(string $purpose): void
    {
        $this->data[self::PURPOSE_ATTRIBUTE] = $purpose;
    }

    public function getAdditionalInfo(): string
    {
        return $this->data[self::ADDITIONAL_INFO_ATTRIBUTE];
    }

    public function setAdditionalInfo(string $additionalInfo): void
    {
        $this->data[self::ADDITIONAL_INFO_ATTRIBUTE] = $additionalInfo;
    }
}
