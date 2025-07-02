<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Courses;

use Dbp\CampusonlineApi\Helpers\ApiException;
use Dbp\CampusonlineApi\PublicRestApi\AbstractApi;
use Dbp\CampusonlineApi\Rest\Tools;
use GuzzleHttp\Exception\GuzzleException;

class CourseApi extends AbstractApi
{
    private const API_PATH = 'co/co-tm-core/course/api';
    private const COURSES_API_PATH = self::API_PATH.'/courses';
    private const SEMESTER_KEY_QUERY_PARAMETER_NAME = 'semester_key';

    public function getCourseByIdentifier(string $identifier): CourseResource
    {
        try {
            $responseData = Tools::decodeJsonResponse(
                $this->connection->getClient()->get(
                    self::COURSES_API_PATH.'/'.rawurlencode($identifier)));

            return new CourseResource($responseData);
        } catch (GuzzleException $guzzleException) {
            throw ApiException::fromGuzzleException($guzzleException);
        }
    }

    public function getCourses(string $semesterKey, ?string $cursor = null, int $maxNumItems = 30, array $options = []): CourseResourcePage
    {
        try {
            // WORKAROUND: CO ignores limit=0
            if ($maxNumItems === 0) {
                return new CourseResourcePage(new \EmptyIterator(), $cursor);
            } else {
                $queryParameters = self::getCursorBasedPaginationQueryParameters($cursor, $maxNumItems);
                $queryParameters[self::SEMESTER_KEY_QUERY_PARAMETER_NAME] = $semesterKey;
                $responseData = Tools::decodeJsonResponse($this->connection->getClient()->get(
                    self::COURSES_API_PATH.'?'.http_build_query($queryParameters)));

                return new CourseResourcePage($this->getCoursesGenerator($responseData), $responseData['nextCursor'] ?? null);
            }
        } catch (GuzzleException $guzzleException) {
            throw ApiException::fromGuzzleException($guzzleException);
        }
    }

    private function getCoursesGenerator(array $responseData): \Generator
    {
        foreach ($responseData['items'] ?? [] as $courseData) {
            yield new CourseResource($courseData);
        }
    }
}
