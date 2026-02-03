<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Organizations;

class PersonOrganisationResource
{
    private const PERSON_UID_ATTRIBUTE = 'personUid';
    private const ORGANISATION_UID_ATTRIBUTE = 'organisationUid';

    public function getPersonUid(): ?string
    {
        return $this->{self::PERSON_UID_ATTRIBUTE} ?? null;
    }

    public function getOrganisationUid(): ?string
    {
        return $this->{self::ORGANISATION_UID_ATTRIBUTE} ?? null;
    }
}
