<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Studies;

use Dbp\CampusonlineApi\PublicRestApi\Resource;

class PartialDegreeProgrammeResource extends Resource
{
    private const IDENTIFIER_ATTRIBUTE = 'identifier';
    private const SUBJECT_ATTRIBUTE = 'subject';
    private const PROGRAMME_ROLE_ATTRIBUTE = 'programmeRole';
    private const ORDER_ATTRIBUTE = 'order';
    private const PARENT_DEGREE_PROGRAMME_UID_ATTRIBUTE = 'parentDegreeProgrammeUid';
    private const CURRICULUM_UID_ATTRIBUTE = 'curriculumUid';
    private const CODE_ATTRIBUTE = 'code';
    private const TYPE_ATTRIBUTE = 'type';

    public function getUid(): ?string
    {
        return $this->resourceData[self::UID_ATTRIBUTE] ?? null;
    }

    public function getIdentifier(): ?string
    {
        return $this->resourceData[self::IDENTIFIER_ATTRIBUTE] ?? null;
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

    public function getProgrammeRoleUid(): ?string
    {
        return $this->resourceData[self::PROGRAMME_ROLE_ATTRIBUTE][self::UID_ATTRIBUTE] ?? null;
    }

    public function getProgrammeRoleCode(): ?string
    {
        return $this->resourceData[self::PROGRAMME_ROLE_ATTRIBUTE][self::CODE_ATTRIBUTE] ?? null;
    }

    public function getProgrammeRoleName(): ?array
    {
        return $this->resourceData[self::PROGRAMME_ROLE_ATTRIBUTE][self::NAME_ATTRIBUTE][self::VALUE_ATTRIBUTE] ?? null;
    }

    public function getProgrammeRoleType(): ?string
    {
        return $this->resourceData[self::PROGRAMME_ROLE_ATTRIBUTE][self::TYPE_ATTRIBUTE] ?? null;
    }

    public function getOrder(): ?int
    {
        return $this->resourceData[self::ORDER_ATTRIBUTE] ?? null;
    }

    public function getParentDegreeProgrammeUid(): ?string
    {
        return $this->resourceData[self::PARENT_DEGREE_PROGRAMME_UID_ATTRIBUTE] ?? null;
    }

    public function getCurriculumUid(): ?string
    {
        return $this->resourceData[self::CURRICULUM_UID_ATTRIBUTE] ?? null;
    }
}
