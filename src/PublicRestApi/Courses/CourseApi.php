<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Courses;

use Dbp\CampusonlineApi\PublicRestApi\AbstractApi;
use Dbp\CampusonlineApi\PublicRestApi\CursorBasedResourcePage;

class CourseApi extends AbstractApi
{
    private const COURSES_API_PATH = Common::API_PATH.'/courses';
    private const SEMESTER_KEY_QUERY_PARAMETER_NAME = 'semester_key';

    public function getCourseByIdentifier(string $identifier): CourseResource
    {
        $resource = $this->getResourceByIdentifier(self::COURSES_API_PATH,
            CourseResource::class, $identifier);
        assert($resource instanceof CourseResource);

        return $resource;
    }

    public function getCourses(string $semesterKey, ?string $cursor = null, int $maxNumItems = 30, array $options = []): CursorBasedResourcePage
    {
        $queryParameters = [
            self::SEMESTER_KEY_QUERY_PARAMETER_NAME => $semesterKey,
        ];

        return $this->getResourcesCursorBased(self::COURSES_API_PATH,
            CourseResource::class, $queryParameters, $cursor, $maxNumItems);
    }
}
