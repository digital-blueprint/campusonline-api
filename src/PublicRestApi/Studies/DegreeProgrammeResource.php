<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Studies;

use Dbp\CampusonlineApi\PublicRestApi\Resource;

class DegreeProgrammeResource extends Resource
{
    private const IDENTIFIER_ATTRIBUTE = 'identifier';
    private const CURRICULUM_UID_ATTRIBUTE = 'curriculumUid';
    private const ADMISSION_TYPE_ATTRIBUTE = 'admissionType';
    private const DEGREE_PROGRAMME_TYPE_ATTRIBUTE = 'degreeProgrammeType';
    private const INTENDED_DEGREE_ATTRIBUTE = 'intendedDegree';
    private const SUBJECT_ATTRIBUTE = 'subject';
    private const PARTIAL_DEGREE_PROGRAMMES_ATTRIBUTE = 'partialDegreeProgrammes';
    private const CODE_ATTRIBUTE = 'code';

    public function getUid(): ?string
    {
        return $this->resourceData[self::UID_ATTRIBUTE] ?? null;
    }

    public function getIdentifier(): ?string
    {
        return $this->resourceData[self::IDENTIFIER_ATTRIBUTE] ?? null;
    }

    public function getCurriculumUid(): ?string
    {
        return $this->resourceData[self::CURRICULUM_UID_ATTRIBUTE] ?? null;
    }

    public function getAdmissionType(): ?string
    {
        return $this->resourceData[self::ADMISSION_TYPE_ATTRIBUTE] ?? null;
    }

    public function getDegreeProgrammeType(): ?string
    {
        return $this->resourceData[self::DEGREE_PROGRAMME_TYPE_ATTRIBUTE] ?? null;
    }

    public function getIntendedDegreeKey(): ?string
    {
        return $this->resourceData[self::INTENDED_DEGREE_ATTRIBUTE][self::KEY_ATTRIBUTE] ?? null;
    }

    public function getIntendedDegreeName(): ?array
    {
        return $this->resourceData[self::INTENDED_DEGREE_ATTRIBUTE][self::NAME_ATTRIBUTE][self::VALUE_ATTRIBUTE] ?? null;
    }

    public function getSubjectUid(): ?string
    {
        return $this->resourceData[self::SUBJECT_ATTRIBUTE][self::UID_ATTRIBUTE] ?? null;
    }

    public function getSubjectCode(): ?string
    {
        return $this->resourceData[self::SUBJECT_ATTRIBUTE][self::CODE_ATTRIBUTE] ?? null;
    }

    public function getSubjectName(): ?array
    {
        return $this->resourceData[self::SUBJECT_ATTRIBUTE][self::NAME_ATTRIBUTE][self::VALUE_ATTRIBUTE] ?? null;
    }

    /**
     * @return PartialDegreeProgrammeResource[]
     */
    public function getPartialDegreeProgrammes(): array
    {
        $partialDegreeProgrammes = [];

        foreach ($this->resourceData[self::PARTIAL_DEGREE_PROGRAMMES_ATTRIBUTE] ?? [] as $partialDegreeProgrammeData) {
            if (is_array($partialDegreeProgrammeData)) {
                $partialDegreeProgrammes[] = new PartialDegreeProgrammeResource($partialDegreeProgrammeData);
            }
        }

        return $partialDegreeProgrammes;
    }
}
