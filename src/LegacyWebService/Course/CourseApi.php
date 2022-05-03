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

    public const TERM_OPTION_NAME = 'term';

    private const COURSE_BY_ID_URI = 'ws/webservice_v1.0/cdm/course/xml';
    private const COURSES_BY_ORGANIZATION_URI = 'ws/webservice_v1.0/cdm/organization/courses/xml';
    private const COURSES_BY_PERSON_URI = 'ws/webservice_v1.0/cdm/person/courses/xml';
    private const STUDENTS_BY_COURSE_URI = 'ws/webservice_v1.0/cdm/course/students/xml';
    private const COURSE_ID_PARAMETER_NAME = 'courseId';
    private const TEACHING_TERM_PARAMETER_NAME = 'teachingTerm';
    private const PERSON_ID_PARAMETER_NAME = 'personID';
    private const TEACHING_TERM_WINTER = 'W';
    private const TEACHING_TERM_SUMMER = 'S';

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
            throw new ApiException("identifier mustn't be empty");
        }

        $parameters = [];
        $parameters[self::COURSE_ID_PARAMETER_NAME] = $identifier;

        $courses = $this->getCoursesInternal(self::COURSE_BY_ID_URI, $parameters, $options, $identifier);
        if (empty($courses)) {
            throw new ApiException("response doesn't contain course with ID ".$identifier, 404, true);
        }
        assert(count($courses) === 1);

        return $courses[0];
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
    public function getCoursesByOrganization(string $orgUnitId, array $options = []): array
    {
        return $this->getCoursesByOrganizationInternal($orgUnitId, $options);
    }

    /**
     * @return CourseData[]
     *
     * @throws ApiException
     */
    public function getCoursesByLecturer(string $lecturerId, array $options = []): array
    {
        if (strlen($lecturerId) === 0) {
            return [];
        }

        $parameters = [];
        $parameters[self::PERSON_ID_PARAMETER_NAME] = $lecturerId;

        return $this->getCoursesInternal(self::COURSES_BY_PERSON_URI, $parameters, $options);
    }

    /**
     * @return PersonData[]
     *
     * @throws ApiException
     */
    public function getStudentsByCourse(string $identifier, array $options = []): array
    {
        if (strlen($identifier) === 0) {
            return [];
        }

        $parameters = [];
        $parameters[self::COURSE_ID_PARAMETER_NAME] = $identifier;

        $responseBody = $this->connection->get(
            self::STUDENTS_BY_COURSE_URI, $options[Api::LANGUAGE_PARAMETER_NAME] ?? '', $parameters);

        return $this->parseStudentsResponse($responseBody);
    }

    /**
     * @return CourseData[]
     *
     * @throws ApiException
     */
    private function getCoursesByOrganizationInternal(string $orgUnitId, array $options): array
    {
        if (strlen($orgUnitId) === 0) {
            return [];
        }

        $parameters = [];
        $parameters[OrganizationUnitApi::ORG_UNIT_ID_PARAMETER_NAME] = $orgUnitId;

        return $this->getCoursesInternal(self::COURSES_BY_ORGANIZATION_URI, $parameters, $options);
    }

    private function getCoursesInternal(string $uri, array $parameters, array $options, string $requestedId = '')
    {
        $teachingTerm = $options[self::TERM_OPTION_NAME] ?? null;
        if ($teachingTerm === self::TEACHING_TERM_WINTER || $teachingTerm === self::TEACHING_TERM_SUMMER) {
            $parameters[self::TEACHING_TERM_PARAMETER_NAME] = $teachingTerm;
        }

        $responseBody = $this->connection->get(
            $uri, $options[Api::LANGUAGE_PARAMETER_NAME] ?? '', $parameters);

        return $this->parseCoursesResponse($responseBody, $requestedId);
    }

    /**
     * @return CourseData[]
     *
     * @throws ApiException
     */
    private function parseCoursesResponse(string $responseBody, string $requestedId = ''): array
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

    /**
     * @return PersonData[]
     *
     * @throws ApiException
     */
    private function parseStudentsResponse(string $responsBody): array
    {
        $students = [];

        $xml = new SimpleXMLElement($responsBody);
        $studentNodes = $xml->xpath('.//person');

        foreach ($studentNodes as $studentNode) {
            $student = $this->parsePersonFromXML($studentNode);
            $students[] = $student;
        }

        return $students;
    }

    private function parseCourseFromXML(SimpleXMLElement $xml): CourseData
    {
        $identifier = trim((string) ($xml->xpath('./courseID')[0] ?? ''));
        $name = trim((string) ($xml->xpath('./courseName/text')[0] ?? ''));
        $language = trim((string) ($xml->xpath('./@language')[0] ?? ''));
        $type = trim((string) ($xml->xpath('./teachingActivity/teachingActivityID')[0] ?? ''));
        $typeName = trim((string) ($xml->xpath('./teachingActivity/teachingActivityName/text')[0] ?? ''));
        $code = trim((string) ($xml->xpath('./courseCode')[0] ?? ''));
        $description = trim((string) ($xml->xpath('./courseDescription')[0] ?? ''));
        $teachingTerm = trim((string) ($xml->xpath('./teachingTerm')[0] ?? ''));
        $numberOfCredits = trim((string) ($xml->xpath('./credits/@hoursPerWeek')[0] ?? ''));
        $levelUrl = trim((string) ($xml->xpath('./level/webLink/href')[0] ?? ''));
        $admissionUrl = trim((string) ($xml->xpath('./admissionInfo/admissionDescription/webLink/href')[0] ?? ''));
        $syllabusUrl = trim((string) ($xml->xpath('./syllabus/webLink/href')[0] ?? ''));
        $examsUrl = trim((string) ($xml->xpath('./exam/infoBlock/webLink/href')[0] ?? ''));
        $datesUrl = trim((string) ($xml->xpath('./teachingActivity/infoBlock/webLink/href')[0] ?? ''));

        $course = new CourseData();
        $course->setIdentifier($identifier);
        $course->setName($name);
        $course->setLanguage($language);
        $course->setType($type);
        $course->setTypeName($typeName);
        $course->setCode($code);
        $course->setDescription($description);
        $course->setTeachingTerm($teachingTerm);
        $course->setNumberOfCredits(floatval($numberOfCredits));
        $course->setLevelUrl($levelUrl);
        $course->setAdmissionUrl($admissionUrl);
        $course->setSyllabusUrl($syllabusUrl);
        $course->setExamsUrl($examsUrl);
        $course->setDatesUrl($datesUrl);

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
