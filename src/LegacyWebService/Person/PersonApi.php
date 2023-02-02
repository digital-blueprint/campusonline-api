<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Person;

use Dbp\CampusonlineApi\Helpers\Page;
use Dbp\CampusonlineApi\Helpers\Pagination;
use Dbp\CampusonlineApi\LegacyWebService\ApiException;
use Dbp\CampusonlineApi\LegacyWebService\Connection;
use Dbp\CampusonlineApi\LegacyWebService\ResourceApi;
use Dbp\CampusonlineApi\LegacyWebService\ResourceData;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SimpleXMLElement;

class PersonApi extends ResourceApi implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const STUDENTS_BY_COURSE_URI = 'ws/webservice_v1.0/cdm/course/students/xml';
    private const COURSE_ID_PARAMETER_NAME = 'courseId';

    private const PERSON_RESOURCE_XML_PATH = './/person';
    private const PERSON_IDENTIFIER_XML_PATH = './personID';

    private const ATTRIBUTE_NAME_TO_XPATH_MAPPING = [
        ResourceData::IDENTIFIER_ATTRIBUTE => self::PERSON_IDENTIFIER_XML_PATH,
        PersonData::GIVEN_NAME_ATTRIBUTE => './name/given',
        PersonData::FAMILY_NAME_ATTRIBUTE => './name/family',
        PersonData::EMAIL_ATTRIBUTE => './contactData/email',
    ];

    public function __construct(Connection $connection, string $rootOrgUnitId)
    {
        parent::__construct($connection, $rootOrgUnitId, self::PERSON_RESOURCE_XML_PATH);
    }

    public function checkConnection()
    {
        // To check if the API can respond with a proper error
        $this->expectGetError(self::STUDENTS_BY_COURSE_URI, [], 400);
        // To check that the token is valid (otherwise we get 401)
        $this->expectGetError(self::STUDENTS_BY_COURSE_URI, [self::COURSE_ID_PARAMETER_NAME => ''], 404);
    }

    /**
     * @throws ApiException
     */
    public function getStudentsByCourse(string $courseId, array $options = []): Page
    {
        if (strlen($courseId) === 0) {
            return Pagination::createEmptyPage($options);
        }

        $parameters = [];
        $parameters[self::COURSE_ID_PARAMETER_NAME] = $courseId;

        return $this->getResourcesInternal(self::STUDENTS_BY_COURSE_URI, $parameters, $options);
    }

    public static function createPersonResource(SimpleXMLElement $node): PersonData
    {
        $returnValue = self::getResourceDataFromXmlIfPassesFiltersStatic($node, self::ATTRIBUTE_NAME_TO_XPATH_MAPPING);
        if (is_array($returnValue) === false) {
            throw new ApiException('internal error');
        }

        $personData = new PersonData();
        $personData->setData($returnValue);

        return $personData;
    }

    protected function createResource(SimpleXMLElement $node): ResourceData
    {
        return new PersonData();
    }

    protected function getAttributeNameToXpathExpressionMapping(): array
    {
        return self::ATTRIBUTE_NAME_TO_XPATH_MAPPING;
    }
}
