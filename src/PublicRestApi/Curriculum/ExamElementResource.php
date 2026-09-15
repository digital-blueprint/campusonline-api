<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Curriculum;

use Dbp\CampusonlineApi\PublicRestApi\Resource;

class ExamElementResource extends Resource
{
    public const UID_ATTRIBUTE = 'uid';
    public const TITLE_ATTRIBUTE = 'title';
    public const TYPE_ATTRIBUTE = 'type';
    public const CODE_ATTRIBUTE = 'code';
    public const PARENT_UID_ATTRIBUTE = 'parentUid';
    public const ORGANISATION_UID_ATTRIBUTE = 'organisationUid';
    public const CURRICULUM_VERSION_UID_ATTRIBUTE = 'curriculumVersionUid';
    public const PART_OF_CURRICULUM_ATTRIBUTE = 'partOfCurriculum';
    public const BRING_FORWARD_OPTION_ATTRIBUTE = 'bringForwardOption';
    public const COURSE_IDENTITY_CODE_UIDS_ATTRIBUTE = 'courseIdentityCodeUids';
    public const SEMESTER_RECOMMENDATION_ATTRIBUTE = 'semesterRecommendation';
    public const SEMESTER_RECOMMENDATION_SHORT_TITLE_ATTRIBUTE = 'shortTitle';
    public const REQUIREMENT_ATTRIBUTE = 'requirement';
    public const CREDITS_ATTRIBUTE = 'credits';
    public const WEIGHT_ATTRIBUTE = 'weight';

    public const WINTER_SEMESTER = 'W';
    public const SUMMER_SEMESTER = 'S';
    public const WINTER_AND_SUMMER_SEMESTER = 'J';
    public const NO_SEMESTER = '-';

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

    public function getCourseIdentityCodeUids(): array
    {
        return $this->resourceData[self::COURSE_IDENTITY_CODE_UIDS_ATTRIBUTE] ?? [];
    }

    public function getSemesterRecommendation(): ?array
    {
        return $this->resourceData[self::SEMESTER_RECOMMENDATION_ATTRIBUTE] ?? null;
    }

    /**
     * Returns either the recommentded semester number as an int, or one of the following strings:
     * self::WINTER_SEMESTER, self::SUMMER_SEMESTER, self::NO_SEMESTER
     *
     * @return int|string
     */
    public function getRecommendedSemester(string $languageTag = self::DEFAULT_LANGUAGE_TAG): mixed
    {
        $recommendedSemester = self::NO_SEMESTER;
        foreach (
            $this->resourceData[self::SEMESTER_RECOMMENDATION_ATTRIBUTE] ?? [] as $semesterRecommendation) {
            $recommendedSemester =
                $semesterRecommendation[self::SEMESTER_RECOMMENDATION_SHORT_TITLE_ATTRIBUTE][self::VALUE_ATTRIBUTE][$languageTag] ??
                self::NO_SEMESTER;
            $semesterNumber = (int) rtrim($recommendedSemester, '.');
            if ($semesterNumber) {
                $recommendedSemester = $semesterNumber;
            }
            if (self::NO_SEMESTER !== $recommendedSemester) {
                break;
            }
        }

        return $recommendedSemester;
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
}
