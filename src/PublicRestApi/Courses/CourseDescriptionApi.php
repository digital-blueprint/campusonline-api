<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Courses;

use Dbp\CampusonlineApi\PublicRestApi\AbstractApi;
use Dbp\CampusonlineApi\PublicRestApi\CursorBasedResourcePage;

class CourseDescriptionApi extends AbstractApi
{
    public const COURSE_UID_QUERY_PARAMETER_NAME = 'course_uid';

    private const COURSE_DESCRIPTIONS_API_PATH = Common::API_PATH.'/course-descriptions';

    public function getCourseDescriptionsByCourseUid(string $courseUid, array $options = []): iterable
    {
        $queryParameters[self::COURSE_UID_QUERY_PARAMETER_NAME] = $courseUid;

        return $this->getResources(self::COURSE_DESCRIPTIONS_API_PATH,
            CourseDescriptionResource::class, $queryParameters);
    }
}
