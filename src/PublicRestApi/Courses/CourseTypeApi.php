<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Courses;

use Dbp\CampusonlineApi\PublicRestApi\AbstractApi;

class CourseTypeApi extends AbstractApi
{
    public const KEY_QUERY_PARAMETER_NAME = 'key';

    private const API_PATH = Common::API_PATH.'/course-types';

    public function getCourseTypeByKey(string $key, array $options = []): CourseTypeResource
    {
        $courseType = $this->getResourceByIdentifier(self::API_PATH,
            CourseTypeResource::class, $key);
        assert($courseType instanceof CourseTypeResource);

        return $courseType;
    }

    /**
     * @return iterable<CourseTypeResource>
     */
    public function getCourseTypes(array $options = []): iterable
    {
        return $this->getResources(self::API_PATH, CourseTypeResource::class);
    }
}
