<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Tests\LegacyWebService\Course;

use Dbp\CampusonlineApi\Helpers\FullPaginator;
use Dbp\CampusonlineApi\Helpers\PartialPaginator;
use Dbp\CampusonlineApi\LegacyWebService\Api;
use Dbp\CampusonlineApi\LegacyWebService\ApiException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class CourseApiTest extends TestCase
{
    /**
     * @var Api
     */
    private $api;

    protected function setUp(): void
    {
        parent::setUp();

        $this->api = new Api('http://localhost', 'token', '1');
        $this->mockResponses([]);
    }

    private function mockResponses(array $responses)
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $this->api->setClientHandler($stack);
    }

    /**
     * @throws ApiException
     */
    public function testGetCourses()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/courses_by_organization_response.xml')),
        ]);

        $paginator = $this->api->Course()->getCourses(['partialPagination' => false]);
        $this->assertInstanceOf(FullPaginator::class, $paginator);
        $this->assertSame(34, $paginator->getTotalNumItems());
        $this->assertSame(34, $paginator->getMaxNumItemsPerPage());
        $this->assertSame(1, $paginator->getCurrentPageNumber());

        $courses = $paginator->getItems();
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
    public function testGetCoursesPagination()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/courses_by_organization_response.xml')),
        ]);

        $paginator = $this->api->Course()->getCourses(['partialPagination' => false, 'perPage' => 30, 'page' => 2]);
        $this->assertInstanceOf(FullPaginator::class, $paginator);
        $this->assertSame(34, $paginator->getTotalNumItems());
        $this->assertSame(30, $paginator->getMaxNumItemsPerPage());
        $this->assertSame(2, $paginator->getCurrentPageNumber());

        $courses = $paginator->getItems();
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
    public function testGetCoursesPartialPagination()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/courses_by_organization_response.xml')),
        ]);

        $paginator = $this->api->Course()->getCourses(['partialPagination' => true, 'perPage' => 30, 'page' => 2]);
        $this->assertInstanceOf(PartialPaginator::class, $paginator);
        $this->assertSame(30, $paginator->getMaxNumItemsPerPage());
        $this->assertSame(2, $paginator->getCurrentPageNumber());

        $courses = $paginator->getItems();
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

        $paginator = $this->api->Course()->getCoursesByOrganization('abc', ['partialPagination' => false]);
        $this->assertInstanceOf(FullPaginator::class, $paginator);
        $this->assertSame(34, $paginator->getTotalNumItems());

        $course = $paginator->getItems()[0];
        $this->assertSame('241333', $course->getIdentifier());
        $this->assertSame('Technische Informatik 1', $course->getName());
        $this->assertSame('german', $course->getLanguage());
        $this->assertSame('448001', $course->getCode());
        $this->assertSame('VO', $course->getType());
        $this->assertSame('', $course->getDescription());
        $this->assertSame(2.0, $course->getNumberOfCredits());
    }
}
