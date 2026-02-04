<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Courses;

use Dbp\CampusonlineApi\PublicRestApi\AbstractApi;

class LectureshipFunctionsApi extends AbstractApi
{
    private const API_PATH = Common::API_PATH.'/lectureship-functions';

    /**
     * @return iterable<LectureshipFunctionsResource>
     */
    public function getLectureshipFunctions(): iterable
    {
        return $this->getResources(self::API_PATH, LectureshipFunctionsResource::class);
    }
}
