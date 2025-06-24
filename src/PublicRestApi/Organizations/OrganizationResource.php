<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Organizations;

class OrganizationResource
{
    private const UID_ATTRIBUTE = 'uid';
    private const CODE_ATTRIBUTE = 'code';

    public function __construct(
        private readonly array $organizationResourceData)
    {
    }

    public function getIdentifier(): ?string
    {
        return $this->organizationResourceData[self::UID_ATTRIBUTE] ?? null;
    }

    public function getCode(): ?string
    {
        return $this->organizationResourceData[self::CODE_ATTRIBUTE] ?? null;
    }
}
