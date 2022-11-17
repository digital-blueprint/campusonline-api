<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Course;

use Dbp\CampusonlineApi\Helpers\Filters;
use Dbp\CampusonlineApi\Helpers\Pagination;
use Dbp\CampusonlineApi\Helpers\Paginator;
use Dbp\CampusonlineApi\LegacyWebService\ApiException;
use Dbp\CampusonlineApi\LegacyWebService\Connection;
use Dbp\CampusonlineApi\LegacyWebService\Organization\OrganizationUnitApi;
use Dbp\CampusonlineApi\LegacyWebService\Person\PersonApi;
use Dbp\CampusonlineApi\LegacyWebService\ResourceApi;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SimpleXMLElement;

class CourseApi extends ResourceApi implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const TERM_OPTION_NAME = 'term';

    private const COURSE_BY_ID_URI = 'ws/webservice_v1.0/cdm/course/xml';
    private const COURSES_BY_ORGANIZATION_URI = 'ws/webservice_v1.0/cdm/organization/courses/xml';
    private const COURSES_BY_PERSON_URI = 'ws/webservice_v1.0/cdm/person/courses/xml';
    private const COURSE_ID_PARAMETER_NAME = 'courseId';
    private const TEACHING_TERM_PARAMETER_NAME = 'teachingTerm';
    private const PERSON_ID_PARAMETER_NAME = 'personID';
    private const TEACHING_TERM_WINTER = 'W';
    private const TEACHING_TERM_SUMMER = 'S';

    private const COURSE_RESOURCE_XML_PATH = '//course';
    private const COURSE_IDENTIFIER_XML_PATH = './courseID';
    private const COURSE_NAME_XML_PATH = './courseName/text';

    public function __construct(Connection $connection, string $rootOrgUnitId)
    {
        parent::__construct($connection, $rootOrgUnitId,
            self::COURSE_RESOURCE_XML_PATH, self::COURSE_IDENTIFIER_XML_PATH);
    }

    public function checkConnection()
    {
        // To check if the API can respond with a proper error
        $this->expectGetError(self::COURSE_BY_ID_URI, [], 400);
        // To check that the token is valid (otherwise we get 401)
        $this->expectGetError(self::COURSE_BY_ID_URI, [self::COURSE_ID_PARAMETER_NAME => ''], 404);
    }

    /**
     * @throws ApiException
     */
    public function getCourseById(string $identifier, array $options = []): CourseData
    {
        if (strlen($identifier) === 0) {
            throw new ApiException("identifier mustn't be empty");
        }

        $options[Filters::IDENTIFIERS_FILTER] = [$identifier];

        $parameters = [];
        $parameters[self::COURSE_ID_PARAMETER_NAME] = $identifier;

        $coursePaginator = $this->getCoursesInternal(self::COURSE_BY_ID_URI, $parameters, $options);
        $courseItems = $coursePaginator->getItems();
        if (empty($courseItems)) {
            throw new ApiException("response doesn't contain course with ID ".$identifier, 404, true);
        }
        assert(count($courseItems) === 1);

        return $courseItems[0];
    }

    /**
     * @throws ApiException
     */
    public function getCourses(array $options = []): Paginator
    {
        return $this->getCoursesByOrganizationInternal($this->rootOrgUnitId, $options);
    }

    /**
     * @throws ApiException
     */
    public function getCoursesByOrganization(string $orgUnitId, array $options = []): Paginator
    {
        return $this->getCoursesByOrganizationInternal($orgUnitId, $options);
    }

    /**
     * @throws ApiException
     */
    public function getCoursesByLecturer(string $lecturerId, array $options = []): Paginator
    {
        if (strlen($lecturerId) === 0) {
            return Pagination::createEmptyPaginator($options);
        }

        $parameters = [];
        $parameters[self::PERSON_ID_PARAMETER_NAME] = $lecturerId;

        return $this->getCoursesInternal(self::COURSES_BY_PERSON_URI, $parameters, $options);
    }

    /**
     * @throws ApiException
     */
    private function getCoursesByOrganizationInternal(string $orgUnitId, array $options): Paginator
    {
        if (strlen($orgUnitId) === 0) {
            return Pagination::createEmptyPaginator($options);
        }

        $parameters = [];
        $parameters[OrganizationUnitApi::ORG_UNIT_ID_PARAMETER_NAME] = $orgUnitId;

        return $this->getCoursesInternal(self::COURSES_BY_ORGANIZATION_URI, $parameters, $options);
    }

    /**
     * @throws ApiException
     */
    private function getCoursesInternal(string $uri, array $parameters, array $options): Paginator
    {
        $teachingTerm = $options[self::TERM_OPTION_NAME] ?? null;
        if ($teachingTerm === self::TEACHING_TERM_WINTER || $teachingTerm === self::TEACHING_TERM_SUMMER) {
            $parameters[self::TEACHING_TERM_PARAMETER_NAME] = $teachingTerm;
        }

        return $this->getResourcesInternal($uri, $parameters, $options);
    }

    protected function createResource(SimpleXMLElement $node, string $identifier): object
    {
        $name = $this->getResourceName($node);
        $language = self::getResourcePropertyOrEmptyString($node, './@language');
        $type = self::getResourcePropertyOrEmptyString($node, './teachingActivity/teachingActivityID');
        $typeName = self::getResourcePropertyOrEmptyString($node, './teachingActivity/teachingActivityName/text');
        $code = self::getResourcePropertyOrEmptyString($node, './courseCode');
        $description = self::getResourcePropertyOrEmptyString($node, './courseDescription');
        $teachingTerm = self::getResourcePropertyOrEmptyString($node, './teachingTerm');
        $numberOfCredits = self::getResourcePropertyOrEmptyString($node, './credits/@hoursPerWeek');
        $levelUrl = self::getResourcePropertyOrEmptyString($node, './level/webLink/href');
        $admissionUrl = self::getResourcePropertyOrEmptyString($node, './admissionInfo/admissionDescription/webLink/href');
        $syllabusUrl = self::getResourcePropertyOrEmptyString($node, './syllabus/webLink/href');
        $examsUrl = self::getResourcePropertyOrEmptyString($node, './exam/infoBlock/webLink/href');
        $datesUrl = self::getResourcePropertyOrEmptyString($node, './teachingActivity/infoBlock/webLink/href');

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
        $personNodes = $node->xpath('./contacts/person');

        foreach ($personNodes as $personNode) {
            $contacts[] = PersonApi::createPersonResource($personNode);
        }
        $course->setContacts($contacts);

        return $course;
    }

    protected function getResourceName(SimpleXMLElement $node): string
    {
        return self::getResourcePropertyOrEmptyString($node, self::COURSE_NAME_XML_PATH);
    }
}
