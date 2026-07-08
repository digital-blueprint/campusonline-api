<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Studies;

use Dbp\CampusonlineApi\PublicRestApi\Resource;

class StudiesResource extends Resource
{
    private const DEGREE_PROGRAMME_UID_ATTRIBUTE = 'degreeProgrammeUid';
    private const PERSON_UID_ATTRIBUTE = 'personUid';
    private const CURRICULUM_VERSION_UID_ATTRIBUTE = 'curriculumVersionUid';
    private const REGISTRATION_STATUS_TYPE_ATTRIBUTE = 'registrationStatusType';
    private const PARTIAL_STUDIES_ATTRIBUTE = 'partialStudies';

    public function getUid(): ?string
    {
        return $this->resourceData[self::UID_ATTRIBUTE] ?? null;
    }

    public function getDegreeProgrammeUid(): ?string
    {
        return $this->resourceData[self::DEGREE_PROGRAMME_UID_ATTRIBUTE] ?? null;
    }

    public function getPersonUid(): ?string
    {
        return $this->resourceData[self::PERSON_UID_ATTRIBUTE] ?? null;
    }

    public function getCurriculumVersionUid(): ?string
    {
        return $this->resourceData[self::CURRICULUM_VERSION_UID_ATTRIBUTE] ?? null;
    }

    public function getRegistrationStatusType(): ?string
    {
        return $this->resourceData[self::REGISTRATION_STATUS_TYPE_ATTRIBUTE] ?? null;
    }

    /**
     * @return PartialStudyResource[]
     */
    public function getPartialStudies(): array
    {
        $partialStudies = [];

        foreach ($this->resourceData[self::PARTIAL_STUDIES_ATTRIBUTE] ?? [] as $partialStudyData) {
            if (is_array($partialStudyData)) {
                $partialStudies[] = new PartialStudyResource($partialStudyData);
            }
        }

        return $partialStudies;
    }
}
