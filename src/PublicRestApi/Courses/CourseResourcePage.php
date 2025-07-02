<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Courses;

readonly class CourseResourcePage
{
    public function __construct(
        private \Iterator $courseResources,
        private ?string $nextCursor)
    {
    }

    public function getCourseResources(): iterable
    {
        return $this->courseResources;
    }

    public function getNextCursor(): ?string
    {
        return $this->nextCursor;
    }
}
