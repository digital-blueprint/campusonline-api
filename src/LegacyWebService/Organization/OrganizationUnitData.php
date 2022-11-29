<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Organization;

use Dbp\CampusonlineApi\LegacyWebService\Address\AddressData;
use Dbp\CampusonlineApi\LegacyWebService\ResourceData;

class OrganizationUnitData extends ResourceData
{
    /** @var string */
    private $code;

    /**
     * @ApiProperty(iri="https://schema.org/url")
     *
     * @var string
     */
    private $url;

    /** @var AddressData|null */
    private $address;

    /**
     * An ID for the organization kind.
     *
     * @var ?string
     */
    private $kindCode;

    /**
     * The translated display name of the organization kind.
     *
     * @var ?string
     */
    private $kindName;

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getAddress(): ?AddressData
    {
        return $this->address;
    }

    public function setAddress(?AddressData $address): void
    {
        $this->address = $address;
    }

    public function getKindCode(): ?string
    {
        return $this->kindCode;
    }

    public function setKindCode(string $kindCode): void
    {
        $this->kindCode = $kindCode;
    }

    public function getKindName(): ?string
    {
        return $this->kindName;
    }

    public function setKindName(string $kindName): void
    {
        $this->kindName = $kindName;
    }
}
