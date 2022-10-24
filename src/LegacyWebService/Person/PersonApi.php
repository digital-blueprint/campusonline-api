<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Person;

use Dbp\CampusonlineApi\Helpers\Pagination;
use Dbp\CampusonlineApi\Helpers\Paginator;
use Dbp\CampusonlineApi\LegacyWebService\ApiException;
use Dbp\CampusonlineApi\LegacyWebService\Connection;
use Dbp\CampusonlineApi\LegacyWebService\ResourceApi;
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

    public function __construct(Connection $connection, string $rootOrgUnitId)
    {
        parent::__construct($connection, $rootOrgUnitId,
            self::PERSON_RESOURCE_XML_PATH, self::PERSON_IDENTIFIER_XML_PATH);
    }

    /**
     * @throws ApiException
     */
    public function getStudentsByCourse(string $courseId, array $options = []): Paginator
    {
        if (strlen($courseId) === 0) {
            return Pagination::createEmptyPaginator($options);
        }

        $parameters = [];
        $parameters[self::COURSE_ID_PARAMETER_NAME] = $courseId;

        return $this->getResourcesInternal(self::STUDENTS_BY_COURSE_URI, $parameters, $options);
    }

    public static function createPersonResource(SimpleXMLElement $node): PersonData
    {
        return self::createPersonResourceInternal($node, self::getResourcePropertyOrEmptyString($node, self::PERSON_IDENTIFIER_XML_PATH));
    }

//    /**
//     * @throws ApiException
//     */
//    private function parseStudentsResponse(string $responseBody, array $options): Paginator
//    {
//        $students = [];
//
//        try {
//            $xml = new SimpleXMLElement($responseBody);
//        } catch (\Exception $e) {
//            throw new ApiException('response body is not in valid XML format');
//        }
//        $nodes = $xml->xpath('.//person');
//
//        $currentPageStartIndex = Pagination::getCurrentPageStartIndex($options);
//
//        $totalNumItems = count($nodes);
//        $maxNumItemsPerPage = Pagination::getMaxNumItemsPerPage($options, $totalNumItems);
//        $currentPageBreakIndex = min($currentPageStartIndex + $maxNumItemsPerPage, $totalNumItems);
//
//        for ($nodeIndex = $currentPageStartIndex; $nodeIndex < $currentPageBreakIndex; ++$nodeIndex) {
//            $node = $nodes[$nodeIndex];
//            $students[] = $this->createPerson($node);
//        }
//
//        if (Pagination::isPartial($options)) {
//            return Pagination::createPartialPaginator($students, $options);
//        } else {
//            return Pagination::createFullPaginator($students, count($students), $options);
//        }
//    }

    protected function createResource(SimpleXMLElement $node, string $identifier): object
    {
        return self::createPersonResourceInternal($node, $identifier);
    }

    private static function createPersonResourceInternal(SimpleXMLElement $node, string $identifier): PersonData
    {
        $givenName = self::getResourcePropertyOrEmptyString($node, './name/given');
        $familyName = self::getResourcePropertyOrEmptyString($node, './name/family');
        $email = self::getResourcePropertyOrEmptyString($node, './contactData/email');

        $person = new PersonData();
        $person->setIdentifier($identifier);
        $person->setGivenName($givenName);
        $person->setFamilyName($familyName);
        $person->setEmail($email);

        return $person;
    }
}
