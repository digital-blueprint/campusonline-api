<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Curriculum;

use Dbp\CampusonlineApi\PublicRestApi\Resource;

class ModuleResource extends Resource
{
    public const TITLE_ATTRIBUTE = 'title';
    public const TYPE_ATTRIBUTE = 'type';
    public const CODE_ATTRIBUTE = 'code';
    public const PARENT_UID_ATTRIBUTE = 'parentUid';
    public const ORGANISATION_UID_ATTRIBUTE = 'organisationUid';
    public const CURRICULUM_VERSION_UID_ATTRIBUTE = 'curriculumVersionUid';
    public const PART_OF_CURRICULUM_ATTRIBUTE = 'partOfCurriculum';
    public const BRING_FORWARD_OPTION_ATTRIBUTE = 'bringForwardOption';
    public const REQUIREMENT_ATTRIBUTE = 'requirement';
    public const CREDITS_ATTRIBUTE = 'credits';
    public const WEIGHT_ATTRIBUTE = 'weight';
    public const TEMPLATE_UID_ATTRIBUTE = 'templateUid';
    public const EXAM_ELEMENTS_ATTRIBUTE = 'examElements';

    public function getUid(): ?string
    {
        return $this->resourceData[self::UID_ATTRIBUTE] ?? null;
    }

    public function getTitle(): ?string
    {
        return $this->resourceData[self::TITLE_ATTRIBUTE][self::VALUE_ATTRIBUTE] ?? null;
    }

    public function getTitleLocalized(string $languageTag = self::DEFAULT_LANGUAGE_TAG): ?string
    {
        return $this->resourceData[self::TITLE_ATTRIBUTE][self::VALUE_ATTRIBUTE][$languageTag] ?? null;
    }

    public function getType(): ?string
    {
        return $this->resourceData[self::TYPE_ATTRIBUTE] ?? null;
    }

    public function getCode(): ?string
    {
        return $this->resourceData[self::CODE_ATTRIBUTE] ?? null;
    }

    public function getParentUid(): ?string
    {
        return $this->resourceData[self::PARENT_UID_ATTRIBUTE] ?? null;
    }

    public function getOrganisationUid(): ?string
    {
        return $this->resourceData[self::ORGANISATION_UID_ATTRIBUTE] ?? null;
    }

    public function getCurriculumVersionUid(): ?string
    {
        return $this->resourceData[self::CURRICULUM_VERSION_UID_ATTRIBUTE] ?? null;
    }

    public function getPartOfCurriculum(): ?bool
    {
        return $this->resourceData[self::PART_OF_CURRICULUM_ATTRIBUTE] ?? null;
    }

    public function getBringForwardOption(): ?bool
    {
        return $this->resourceData[self::BRING_FORWARD_OPTION_ATTRIBUTE] ?? null;
    }

    public function getRequirement(): ?bool
    {
        return $this->resourceData[self::REQUIREMENT_ATTRIBUTE] ?? null;
    }

    public function getCredits(): ?float
    {
        return $this->resourceData[self::CREDITS_ATTRIBUTE] ?? null;
    }

    public function getWeight(): ?int
    {
        return $this->resourceData[self::WEIGHT_ATTRIBUTE] ?? null;
    }

    public function getTemplateUid(): ?string
    {
        return $this->resourceData[self::TEMPLATE_UID_ATTRIBUTE] ?? null;
    }

    /**
     * @return array<int, array>
     */
    public function getExamElements(): array
    {
        return $this->resourceData[self::EXAM_ELEMENTS_ATTRIBUTE] ?? [];
    }

    /**
     * @return array<int, ExamElementResource>
     */
    public function getExamElementResources(): array
    {
        return array_map(
            fn (array $examElementData) => new ExamElementResource($examElementData),
            $this->getExamElements()
        );
    }
}
