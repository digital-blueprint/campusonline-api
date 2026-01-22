<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Courses;

use Dbp\CampusonlineApi\PublicRestApi\AbstractApi;

class CourseGroupApi extends AbstractApi
{
    public const COURSE_UID_QUERY_PARAMETER_NAME = 'course_uid';

    private const API_PATH = Common::API_PATH.'/course-groups';

    /**
     * @return iterable<CourseGroupResource>
     */
    public function getCourseGroupsByCourseUid(string $courseUid, array $options = []): iterable
    {
        return $this->getResources(
            self::API_PATH,
            CourseGroupResource::class,
            [
                self::COURSE_UID_QUERY_PARAMETER_NAME => $courseUid,
            ]);
    }
}
