<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Course;

use Dbp\CampusonlineApi\LegacyWebService\Api;
use Dbp\CampusonlineApi\LegacyWebService\Connection;
use Dbp\CampusonlineApi\LegacyWebService\Person\PersonData;
use Dbp\CampusonlineApi\Rest\ApiException;
use SimpleXMLElement;

class CourseApi
{
    private const URI = 'ws/webservice_v1.0/cdm/course/xml';

    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @throws ApiException
     */
    public function getCourseById(string $identifier, array $options = []): ?CourseData
    {
        $courses = $this->getCoursesInternal($identifier, $options);
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
        return $this->getCoursesInternal('', $options);
    }

    /**
     * @return CourseData[]
     *
     * @throws ApiException
     */
    private function getCoursesInternal(string $courseId, array $options): array
    {
        $parameters = [];
        $responseBody = $this->connection->get(
            self::URI, $options[Api::LANGUAGE_PARAMETER_NAME] ?? '', $parameters);

        return $this->parseResponse($responseBody, $courseId);
    }

    /**
     * @return CourseData[]
     *
     * @throws ApiException
     */
    private function parseResponse(string $responseBody, string $requestedIdentifier): array
    {
        $courses = [];

        try {
            $xml = new SimpleXMLElement($responseBody);
        } catch (\Exception $e) {
            throw new ApiException('response body is not in valid XML format');
        }
        $nodes = $xml->xpath('//course');

        foreach ($nodes as $node) {
            $wasIdFound = false;
            $course = $this->parseCourseFromXML($node);
            $identifier = $course->getIdentifier();
            if ($identifier === '') {
                continue;
            } elseif ($requestedIdentifier !== '') {
                if ($identifier === $requestedIdentifier) {
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
        $language = trim((string) ($xml->xpath('./@language')[0] ?? ''));
        $educationalLevel = trim((string) ($xml->xpath('./@typeName')[0] ?? ''));
        $courseCode = trim((string) ($xml->xpath('./courseCode')[0] ?? ''));
        $description = trim((string) ($xml->xpath('./courseDescription')[0] ?? ''));
        $citation = trim((string) ($xml->xpath('./level/webLink/href')[0] ?? ''));
        $numberOfCredits = trim((string) ($xml->xpath('./credits/@hoursPerWeek')[0] ?? ''));
        $occupationalCredentialAwarded = trim((string) ($xml->xpath('./learningObjectives')[0] ?? ''));
        $availableLanguage = trim((string) ($xml->xpath('./instructionLanguage/@teachingLang')[0] ?? ''));
        $url = trim((string) ($xml->xpath('./exam/infoBlock/webLink/href/text()')[0] ?? ''));
        $name = trim((string) ($xml->xpath('./courseName/text')[0] ?? ''));
        // TODO: what to do with remaining course data present in the XML
        // $coursePrerequisites = trim((string) ($xml->xpath('./description/attribute[@attrID="roomCode"]')[0] ?? ''));
        // $learningResourceType = trim((string) ($xml->xpath('./description/attribute[@attrID="roomCode"]')[0] ?? ''));

        $course = new CourseData();
        $course->setIdentifier($identifier);
        $course->setLanguage($language);
        $course->setEducationalLevel($educationalLevel);
        $course->setCourseCode($courseCode);
        $course->setDescription($description);
        $course->setCitation($citation);
        $course->setNumberOfCredits($numberOfCredits);
        $course->setOccupationalCredentialAwarded($occupationalCredentialAwarded);
        $course->setAvailableLanguage($availableLanguage);
        $course->setUrl($url);
        $course->setName($name);

        $maintainers = [];
        $persons = $xml->xpath('./contacts/person');

        foreach ($persons as $person) {
            $maintainer = $this->parsePersonFromXML($person);
            $maintainers[] = $maintainer;
        }
        $course->setMaintainer($maintainers);

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
