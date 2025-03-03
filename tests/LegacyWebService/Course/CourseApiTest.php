<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Tests\LegacyWebService\Course;

use Dbp\CampusonlineApi\Helpers\Filters;
use Dbp\CampusonlineApi\Helpers\Page;
use Dbp\CampusonlineApi\LegacyWebService\Api;
use Dbp\CampusonlineApi\LegacyWebService\ApiException;
use Dbp\CampusonlineApi\LegacyWebService\Course\CourseData;
use Dbp\CampusonlineApi\LegacyWebService\ResourceApi;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class CourseApiTest extends TestCase
{
    /**
     * @var Api
     */
    private $api;

    protected function setUp(): void
    {
        parent::setUp();

        $this->api = new Api('http://localhost', 'token', '0', null,
            new ArrayAdapter(3600, true, 3600, 356), 3600);
        $this->mockResponses([]);
    }

    private function mockResponses(array $responses)
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $this->api->setClientHandler($stack);
    }

    public function testCheckConnection()
    {
        $this->mockResponses([
            new Response(400, ['Content-Type' => 'text/xml;charset=utf-8'], ''),
            new Response(404, ['Content-Type' => 'text/xml;charset=utf-8'], ''),
        ]);

        $this->api->Course()->checkConnection();
        $this->expectNotToPerformAssertions();
    }

    /**
     * @throws ApiException
     */
    public function testGetCourses()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/courses_by_organization_response.xml')),
        ]);

        $page = $this->api->Course()->getCourses(['partialPagination' => false]);
        $this->assertInstanceOf(Page::class, $page);
        $this->assertSame(34, $page->getMaxNumItemsPerPage());
        $this->assertSame(1, $page->getCurrentPageNumber());

        $courses = $page->getItems();
        $this->assertCount(34, $courses);
        $course = $courses[0];

        $this->assertSame('241333', $course->getIdentifier());
        $this->assertSame('Technische Informatik 1', $course->getName());
        $this->assertSame('german', $course->getLanguage());
        $this->assertSame('448001', $course->getCode());
        $this->assertSame('VO', $course->getType());
        $this->assertSame('', $course->getDescription());
        $this->assertSame(2.0, $course->getNumberOfCredits());
    }

    /**
     * @throws ApiException
     */
    public function testGetCoursesFiltered()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/courses_by_organization_response.xml')),
        ]);

        // case-insensitive name filter -> match
        $options = [];
        ResourceApi::addFilter($options, CourseData::NAME_ATTRIBUTE, Filters::CONTAINS_CI_OPERATOR, 'seminar', Filters::LOGICAL_OR_OPERATOR);
        $page = $this->api->Course()->getCourses($options);

        $courses = $page->getItems();
        $this->assertCount(3, $courses);

        $this->assertSame('Seminar/Project Technical Informatics', $courses[0]->getName());
        $this->assertSame('Elektro-/Informationstechnisches Seminarprojekt', $courses[1]->getName());
        $this->assertSame('Mobile Computing, Seminar', $courses[2]->getName());

        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/courses_by_organization_response.xml')),
        ]);

        // case-sensitive name filter -> NO match
        $options = [];
        ResourceApi::addFilter($options, CourseData::NAME_ATTRIBUTE, Filters::CONTAINS_OPERATOR, 'seminar', Filters::LOGICAL_OR_OPERATOR);
        $page = $this->api->Course()->getCourses($options);

        $courses = $page->getItems();
        $this->assertCount(0, $courses);
    }

    /**
     * @throws ApiException
     */
    public function testGetCoursesPagination()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/courses_by_organization_response.xml')),
        ]);

        $page = $this->api->Course()->getCourses(['perPage' => 30, 'page' => 2]);
        $this->assertInstanceOf(Page::class, $page);
        $this->assertSame(30, $page->getMaxNumItemsPerPage());
        $this->assertSame(2, $page->getCurrentPageNumber());

        $courses = $page->getItems();
        $this->assertCount(4, $courses);
        $course = $courses[0];

        $this->assertSame('238147', $course->getIdentifier());
        $this->assertSame('AK Embedded Automotive Systems', $course->getName());
        $this->assertSame('german', $course->getLanguage());
        $this->assertSame('448112', $course->getCode());
        $this->assertSame('PV', $course->getType());
        $this->assertSame('', $course->getDescription());
        $this->assertSame(2.0, $course->getNumberOfCredits());
    }

    /**
     * @throws ApiException
     */
    public function testGetCourses500()
    {
        $this->mockResponses([
            new Response(500, ['Content-Type' => 'text/xml;charset=utf-8'], ''),
        ]);

        $this->expectException(ApiException::class);
        $this->api->Course()->getCourses();
    }

    /**
     * @throws ApiException
     */
    public function testGetCoursesInvalidXML()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/course_response_invalid_xml.xml')),
        ]);

        $this->expectException(ApiException::class);
        $this->api->Course()->getCourses();
    }

    /**
     * @throws ApiException
     */
    public function testGetCourseById()
    {
        $this->mockResponses([
            // new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/RoomsResponse.xml')),
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/course_by_id_response.xml')),
        ]);

        $course = $this->api->Course()->getCourseById('240759');

        $this->assertSame('240759', $course->getIdentifier());
        $this->assertSame('Computational Intelligence', $course->getName());
        $this->assertSame('german', $course->getLanguage());
        $this->assertSame('442071', $course->getCode());
        $this->assertSame('UE', $course->getType());
        $this->assertSame('Anwendungen der wichtigsten Methoden aus den Bereichen Maschinelles Lernen und Neuronale Netzwerke. Praxis-orientierte Probleme des Maschinellen Lernens im Allgemeinen und der einzelnen Ansätze im speziellen werden aufgezeigt und die entsprechende Lösungsansätze präsentiert.', $course->getDescription());
        $this->assertSame(1.0, $course->getNumberOfCredits());

        $contacts = $course->getContacts();

        $this->assertSame('DEADBEEF2', $contacts[0]->getIdentifier());
    }

    /**
     * @throws ApiException
     */
    public function testGetCourseByIdNotFound()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/course_by_id_response.xml')),
        ]);

        $this->expectException(ApiException::class);
        $this->expectExceptionCode(404);
        $this->api->Course()->getCourseById('123');
    }

    /**
     * @throws ApiException
     */
    public function testGetCourseById500()
    {
        $this->mockResponses([
            new Response(500, ['Content-Type' => 'text/xml;charset=utf-8'], ''),
        ]);

        $this->expectException(ApiException::class);
        $this->api->Course()->getCourseById('123');
    }

    /**
     * @throws ApiException
     */
    public function testGetCoursesByOrganization()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/courses_by_organization_response.xml')),
        ]);

        $page = $this->api->Course()->getCoursesByOrganization('abc');
        $this->assertInstanceOf(Page::class, $page);

        $course = $page->getItems()[0];
        $this->assertSame('241333', $course->getIdentifier());
        $this->assertSame('Technische Informatik 1', $course->getName());
        $this->assertSame('german', $course->getLanguage());
        $this->assertSame('448001', $course->getCode());
        $this->assertSame('VO', $course->getType());
        $this->assertSame('', $course->getDescription());
        $this->assertSame(2.0, $course->getNumberOfCredits());
    }
}
