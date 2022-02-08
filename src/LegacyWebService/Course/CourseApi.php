<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Course;

use Dbp\CampusonlineApi\LegacyWebService\Api;
use Dbp\CampusonlineApi\LegacyWebService\ApiException;
use Dbp\CampusonlineApi\LegacyWebService\Connection;
use Dbp\CampusonlineApi\LegacyWebService\Organization\OrganizationUnitApi;
use Dbp\CampusonlineApi\LegacyWebService\Person\PersonData;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SimpleXMLElement;

class CourseApi implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const COURSE_BY_ID_URI = 'ws/webservice_v1.0/cdm/course/xml';
    private const COURSE_BY_ORGANIZATION_URI = 'ws/webservice_v1.0/cdm/organization/courses/xml';
    private const COURSE_ID_PARAMETER_NAME = 'courseId';

    private $connection;
    private $rootOrgUnitId;

    public function __construct(Connection $connection, string $rootOrgUnitId)
    {
        $this->connection = $connection;
        $this->rootOrgUnitId = $rootOrgUnitId;
    }

    /**
     * @throws ApiException
     */
    public function getCourseById(string $identifier, array $options = []): ?CourseData
    {
        if (strlen($identifier) === 0) {
            return null;
        }

        $parameters = [];
        $parameters[self::COURSE_ID_PARAMETER_NAME] = $identifier;

        try {
            $responseBody = $this->connection->get(
                self::COURSE_BY_ID_URI, $options[Api::LANGUAGE_PARAMETER_NAME] ?? '', $parameters);
        } catch (ApiException $e) {
            if ($e->getCode() === Api::HTTP_STATUS_NOT_FOUND) {
                return null;
            } else {
                throw $e;
            }
        }

        $courses = $this->parseCoursesResponse($responseBody, $identifier);
        assert(count($courses) <= 1);

        return empty($courses) ? null : $courses[0];
    }

    /**
     * @return CourseData[]
     *
     * @throws ApiException
     */
    public function getCourses(array $options = []): array
    {
        return $this->getCoursesByOrganizationInternal($this->rootOrgUnitId, $options);
    }

    /**
     * @return CourseData[]
     *
     * @throws ApiException
     */
    public function getCoursesByOrganization(string $identifier, array $options = []): array
    {
        return $this->getCoursesByOrganizationInternal($identifier, $options);
    }

    /**
     * @return CourseData[]
     *
     * @throws ApiException
     */
    private function getCoursesByOrganizationInternal(string $identifier, array $options): array
    {
        if (strlen($identifier) === 0) {
            return [];
        }
        $parameters = [];
        $parameters[OrganizationUnitApi::ORG_UNIT_ID_PARAMETER_NAME] = $identifier;

        $responseBody = $this->connection->get(
            self::COURSE_BY_ORGANIZATION_URI, $options[Api::LANGUAGE_PARAMETER_NAME] ?? '', $parameters);

        return $this->parseCoursesResponse($responseBody, '');
    }

    /**
     * @return CourseData[]
     *
     * @throws ApiException
     */
    private function parseCoursesResponse(string $responseBody, string $requestedId): array
    {
        $courses = [];

        try {
            $xml = new SimpleXMLElement($responseBody);
        } catch (\Exception $e) {
            throw new ApiException('response body is not in valid XML format');
        }
        $nodes = $xml->xpath('//course');

        foreach ($nodes as $node) {
            $course = $this->parseCourseFromXML($node);
            $identifier = $course->getIdentifier();
            if ($identifier === '') {
                continue;
            }

            $wasIdFound = false;
            if ($requestedId !== '') {
                if ($identifier === $requestedId) {
                    $wasIdFound = true;
                } else {
                    continue;
                }
            }

            $courses[] = $course;

            if ($wasIdFound) {
                break;
            }
        }

        return $courses;
    }

    private function parseCourseFromXML(SimpleXMLElement $xml): CourseData
    {
        $identifier = trim((string) ($xml->xpath('./courseID')[0] ?? ''));
        $name = trim((string) ($xml->xpath('./courseName/text')[0] ?? ''));
        $language = trim((string) ($xml->xpath('./@language')[0] ?? ''));
        $type = trim((string) ($xml->xpath('./teachingActivity/teachingActivityID')[0] ?? ''));
        $code = trim((string) ($xml->xpath('./courseCode')[0] ?? ''));
        $description = trim((string) ($xml->xpath('./courseDescription')[0] ?? ''));
        $teachingTerm = trim((string) ($xml->xpath('./teachingTerm')[0] ?? ''));
        $numberOfCredits = trim((string) ($xml->xpath('./credits/@hoursPerWeek')[0] ?? ''));

        $course = new CourseData();
        $course->setIdentifier($identifier);
        $course->setName($name);
        $course->setLanguage($language);
        $course->setType($type);
        $course->setCode($code);
        $course->setDescription($description);
        $course->setTeachingTerm($teachingTerm);
        $course->setNumberOfCredits($numberOfCredits);

        $contacts = [];
        $personNodes = $xml->xpath('./contacts/person');

        foreach ($personNodes as $personNode) {
            $contacts[] = $this->parsePersonFromXML($personNode);
        }
        $course->setContacts($contacts);

        return $course;
    }

    private function parsePersonFromXML(SimpleXMLElement $xml): PersonData
    {
        $identifier = trim((string) ($xml->xpath('./personID')[0] ?? ''));
        $givenName = trim((string) ($xml->xpath('./name/given')[0] ?? ''));
        $familyName = trim((string) ($xml->xpath('./name/family')[0] ?? ''));
        $email = trim((string) ($xml->xpath('./contactData/email')[0] ?? ''));

        $person = new PersonData();
        $person->setIdentifier($identifier);
        $person->setGivenName($givenName);
        $person->setFamilyName($familyName);
        $person->setEmail($email);

        return $person;
    }
}
