<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Studies;

use Dbp\CampusonlineApi\PublicRestApi\Resource;

class PartialStudyResource extends Resource
{
    private const PARTIAL_DEGREE_PROGRAMME_UID_ATTRIBUTE = 'partialDegreeProgrammeUid';
    private const CURRICULUM_VERSION_UID_ATTRIBUTE = 'curriculumVersionUid';
    private const REGISTRATION_STATUS_TYPE_ATTRIBUTE = 'registrationStatusType';

    public function getUid(): ?string
    {
        return $this->resourceData[self::UID_ATTRIBUTE] ?? null;
    }

    public function getPartialDegreeProgrammeUid(): ?string
    {
        return $this->resourceData[self::PARTIAL_DEGREE_PROGRAMME_UID_ATTRIBUTE] ?? null;
    }

    public function getCurriculumVersionUid(): ?string
    {
        return $this->resourceData[self::CURRICULUM_VERSION_UID_ATTRIBUTE] ?? null;
    }

    public function getRegistrationStatusType(): ?string
    {
        return $this->resourceData[self::REGISTRATION_STATUS_TYPE_ATTRIBUTE] ?? null;
    }
}
