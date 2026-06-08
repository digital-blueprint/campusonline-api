<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Courses;

use Dbp\CampusonlineApi\PublicRestApi\AbstractApi;

class LectureshipApi extends AbstractApi
{
    public const COURSE_UID_QUERY_PARAMETER_NAME = 'course_uid';

    private const API_PATH = Common::API_PATH.'/lectureships';

    /**
     * @return iterable<LectureshipResource>
     */
    public function getLectureshipsFor(array $courseUids, array $queryParameters = [], array $options = []): iterable
    {
        $queryParameters[self::COURSE_UID_QUERY_PARAMETER_NAME] = $courseUids;

        return $this->getResources(self::API_PATH,
            LectureshipResource::class,
            $queryParameters);
    }
}
