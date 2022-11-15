<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Organization;

use Dbp\CampusonlineApi\LegacyWebService\Address\AddressData;
use Dbp\CampusonlineApi\LegacyWebService\ResourceData;

class OrganizationUnitData extends ResourceData
{
    /** @var string */
    private $code;

    /** @var string */
    private $type;

    /**
     * @ApiProperty(iri="https://schema.org/url")
     *
     * @var string
     */
    private $url;

    /** @var AddressData|null */
    private $address;

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
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
}
