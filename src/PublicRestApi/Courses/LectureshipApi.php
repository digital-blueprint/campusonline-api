<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Courses;

use Dbp\CampusonlineApi\PublicRestApi\AbstractApi;

class LectureshipApi extends AbstractApi
{
    public const COURSE_UID_QUERY_PARAMETER_NAME = 'course_uid';

    private const LECTURESHIPS_API_PATH = Common::API_PATH.'/lectureships';

    /**
     * @return iterable<LectureshipResource>
     */
    public function getLectureships(array $queryParameters = [], array $options = []): iterable
    {
        return $this->getResources(self::LECTURESHIPS_API_PATH,
            LectureshipResource::class,
            $queryParameters);
    }

    /**
     * @return iterable<LectureshipResource>
     */
    public function getLectureshipsByCourseUid(string $courseUid, array $options = []): iterable
    {
        return $this->getLectureships([
            self::COURSE_UID_QUERY_PARAMETER_NAME => $courseUid,
        ], $options);
    }
}
