<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Organizations;

use Dbp\CampusonlineApi\PublicRestApi\Resource;

class PersonOrganisationResource extends Resource
{
    private const PERSON_UID_ATTRIBUTE = 'personUid';
    private const ORGANISATION_UID_ATTRIBUTE = 'organisationUid';

    public function getPersonUid(): ?string
    {
        return $this->resourceData[self::PERSON_UID_ATTRIBUTE] ?? null;
    }

    public function getOrganisationUid(): ?string
    {
        return $this->resourceData[self::ORGANISATION_UID_ATTRIBUTE] ?? null;
    }
}
