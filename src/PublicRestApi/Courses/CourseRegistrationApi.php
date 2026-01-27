<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Courses;

use Dbp\CampusonlineApi\PublicRestApi\AbstractApi;

class CourseRegistrationApi extends AbstractApi
{
    public const COURSE_UID_QUERY_PARAMETER_NAME = 'course_uid';

    private const API_PATH = Common::API_PATH.'/registrations';

    /**
     * @return iterable<CourseRegistrationResource>
     */
    public function getCourseRegistrations(array $queryParameters = [], array $options = []): iterable
    {
        return $this->getResources(self::API_PATH,
            CourseRegistrationResource::class,
            $queryParameters);
    }

    /**
     * @return iterable<CourseRegistrationResource>
     */
    public function getCourseRegistrationsByCourseUid(string $courseUid, array $options = []): iterable
    {
        return $this->getCourseRegistrations([
            self::COURSE_UID_QUERY_PARAMETER_NAME => $courseUid,
        ], $options);
    }
}
