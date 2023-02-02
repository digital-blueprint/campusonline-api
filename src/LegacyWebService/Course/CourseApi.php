<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Course;

use Dbp\CampusonlineApi\Helpers\Page;
use Dbp\CampusonlineApi\Helpers\Pagination;
use Dbp\CampusonlineApi\LegacyWebService\ApiException;
use Dbp\CampusonlineApi\LegacyWebService\Connection;
use Dbp\CampusonlineApi\LegacyWebService\Organization\OrganizationUnitApi;
use Dbp\CampusonlineApi\LegacyWebService\Person\PersonApi;
use Dbp\CampusonlineApi\LegacyWebService\ResourceApi;
use Dbp\CampusonlineApi\LegacyWebService\ResourceData;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SimpleXMLElement;

class CourseApi extends ResourceApi implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    // Request attributes:
    public const TERM_OPTION_NAME = 'term';
    private const COURSE_BY_ID_URI = 'ws/webservice_v1.0/cdm/course/xml';
    private const COURSES_BY_ORGANIZATION_URI = 'ws/webservice_v1.0/cdm/organization/courses/xml';
    private const COURSES_BY_PERSON_URI = 'ws/webservice_v1.0/cdm/person/courses/xml';
    private const COURSE_ID_PARAMETER_NAME = 'courseId';
    private const TEACHING_TERM_PARAMETER_NAME = 'teachingTerm';
    private const PERSON_ID_PARAMETER_NAME = 'personID';
    private const TEACHING_TERM_WINTER = 'W';
    private const TEACHING_TERM_SUMMER = 'S';

    // Response attributes:
    private const COURSE_RESOURCE_XML_PATH = '//course';
    private const COURSE_IDENTIFIER_XML_PATH = './courseID';
    private const COURSE_NAME_XML_PATH = './courseName/text';
    private const COURSE_CONTACTS_PERSON_XML_PATH = './contacts/person';

    private const ATTRIBUTE_NAME_TO_XPATH_MAPPING = [
        ResourceData::IDENTIFIER_ATTRIBUTE => self::COURSE_IDENTIFIER_XML_PATH,
        CourseData::NAME_ATTRIBUTE => self::COURSE_NAME_XML_PATH,
        CourseData::LANGUAGE_ATTRIBUTE => './@language',
        CourseData::TYPE_ATTRIBUTE => './teachingActivity/teachingActivityID',
        CourseData::TYPE_NAME_ATTRIBUTE => './teachingActivity/teachingActivityName/text',
        CourseData::CODE_ATTRIBUTE => './courseCode',
        CourseData::DESCRIPTION_ATTRIBUTE => './courseDescription',
        CourseData::TEACHING_TERM_ATTRIBUTE => './teachingTerm',
        CourseData::NUMBER_OF_CREDITS_ATTRIBUTE => './credits/@hoursPerWeek',
        CourseData::LEVEL_URL_ATTRIBUTE => './level/webLink/href',
        CourseData::ADMISSION_URL_ATTRIBUTE => './admissionInfo/admissionDescription/webLink/href',
        CourseData::SYLLABUS_URL_ATTRIBUTE => './syllabus/webLink/href',
        CourseData::EXAMS_URL_ATTRIBUTE => './exam/infoBlock/webLink/href',
        CourseData::DATES_URL_ATTRIBUTE => './teachingActivity/infoBlock/webLink/href',
        ];

    public function __construct(Connection $connection, string $rootOrgUnitId)
    {
        parent::__construct($connection, $rootOrgUnitId, self::COURSE_RESOURCE_XML_PATH);
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

        ResourceApi::addIdFilter($options, $identifier);

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
    public function getCourses(array $options = []): Page
    {
        return $this->getCoursesByOrganizationInternal($this->rootOrgUnitId, $options);
    }

    /**
     * @throws ApiException
     */
    public function getCoursesByOrganization(string $orgUnitId, array $options = []): Page
    {
        return $this->getCoursesByOrganizationInternal($orgUnitId, $options);
    }

    /**
     * @throws ApiException
     */
    public function getCoursesByLecturer(string $lecturerId, array $options = []): Page
    {
        if (strlen($lecturerId) === 0) {
            return Pagination::createEmptyPage($options);
        }

        $parameters = [];
        $parameters[self::PERSON_ID_PARAMETER_NAME] = $lecturerId;

        return $this->getCoursesInternal(self::COURSES_BY_PERSON_URI, $parameters, $options);
    }

    /**
     * @throws ApiException
     */
    private function getCoursesByOrganizationInternal(string $orgUnitId, array $options): Page
    {
        if (strlen($orgUnitId) === 0) {
            return Pagination::createEmptyPage($options);
        }

        $parameters = [];
        $parameters[OrganizationUnitApi::ORG_UNIT_ID_PARAMETER_NAME] = $orgUnitId;

        return $this->getCoursesInternal(self::COURSES_BY_ORGANIZATION_URI, $parameters, $options);
    }

    /**
     * @throws ApiException
     */
    private function getCoursesInternal(string $uri, array $parameters, array $options): Page
    {
        $teachingTerm = $options[self::TERM_OPTION_NAME] ?? null;
        if ($teachingTerm === self::TEACHING_TERM_WINTER || $teachingTerm === self::TEACHING_TERM_SUMMER) {
            $parameters[self::TEACHING_TERM_PARAMETER_NAME] = $teachingTerm;
        }

        return $this->getResourcesInternal($uri, $parameters, $options);
    }

    protected function createResource(SimpleXMLElement $node): ResourceData
    {
        return new CourseData();
    }

    protected function getResourceDataFromXmlIfPassesFilters(SimpleXMLElement $node, array $attributeNameToXpathExpressionMapping, array $filters = [])
    {
        $resourceData = parent::getResourceDataFromXmlIfPassesFilters($node, $attributeNameToXpathExpressionMapping, $filters);
        if (is_array($resourceData)) {
            $contacts = [];
            foreach ($node->xpath(self::COURSE_CONTACTS_PERSON_XML_PATH) as $personNode) {
                $contacts[] = PersonApi::createPersonResource($personNode);
            }
            $resourceData[CourseData::CONTACTS_ATTRIBUTE] = $contacts;
        }

        return $resourceData;
    }

    protected function getAttributeNameToXpathExpressionMapping(): array
    {
        return self::ATTRIBUTE_NAME_TO_XPATH_MAPPING;
    }
}
