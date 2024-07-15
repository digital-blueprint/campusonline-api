<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Room;

use Dbp\CampusonlineApi\LegacyWebService\ResourceData;

class RoomData extends ResourceData
{
    /** Search filters: Pass, if ANY of the given search filters passes or if NONE is given */
    /** @var string Partial, case-insensitive text search on the 'name' attribute. Passes if filter is empty. */
    public const NAME_SEARCH_FILTER_NAME = 'nameSearchFilter';

    /** Search filters: Pass, if ANY of the given search filters passes or if NONE is given */
    /** @var string Partial, case-insensitive text search on the 'additionalInfo' attribute. Passes if filter is empty. */
    public const ADDITIONAL_INFO_SEARCH_FILTER_NAME = 'additionalInfoSearchFilter';

    public const CODE_ATTRIBUTE = 'code';
    public const ADDRESS_ATTRIBUTE = 'address';
    public const URL_ATTRIBUTE = 'url';
    public const FLOOR_SIZE_ATTRIBUTE = 'floorSize';
    public const PURPOSE_ATTRIBUTE = 'purpose';
    public const PURPOSE_ID_ATTRIBUTE = 'purposeId';
    public const ADDITIONAL_INFO_ATTRIBUTE = 'additionalInfo';
    public const ORGANIZATIONS_ATTRIBUTE = 'organizations';

    public function getCode(): string
    {
        return $this->data[self::CODE_ATTRIBUTE];
    }

    public function setCode(string $code): void
    {
        $this->data[self::CODE_ATTRIBUTE] = $code;
    }

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
        return (float) $this->data[self::FLOOR_SIZE_ATTRIBUTE];
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

    public function getOrganizations(): array
    {
        return $this->data[self::ORGANIZATIONS_ATTRIBUTE];
    }

    public function setOrganizations(array $organizations): void
    {
        $this->data[self::ORGANIZATIONS_ATTRIBUTE] = $organizations;
    }
}
