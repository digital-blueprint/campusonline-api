<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Rest\ResearchProject;

/**
 * @deprecated Use Dbp\CampusonlineApi\Rest\Generic\ApiResource instead
 */
class ResearchProjectData
{
    /** @var string Partial, case-insensitive text search on the 'title' attribute. */
    public const TITLE_SEARCH_FILTER_NAME = 'titleSearchFilter';

    private $identifier;
    private $title;
    private $description;
    private $startDate;
    private $endDate;

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    public function setStartDate(?string $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    public function setEndDate(?string $endDate): void
    {
        $this->endDate = $endDate;
    }
}
