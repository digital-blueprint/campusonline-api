<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Courses;

use Dbp\CampusonlineApi\PublicRestApi\AbstractApi;

class LectureshipApi extends AbstractApi
{
    private const LECTURESHIPS_API_PATH = Common::API_PATH.'/lectureships';
    private const COURSE_UID_QUERY_PARAMETER_NAME = 'course_uid';

    /**
     * @return iterable<LectureshipResource>
     */
    public function getLectureshipsByCourseUid(string $courseUid): iterable
    {
        return $this->getResources(self::LECTURESHIPS_API_PATH,
            LectureshipResource::class,
            [self::COURSE_UID_QUERY_PARAMETER_NAME => $courseUid]);
    }
}
