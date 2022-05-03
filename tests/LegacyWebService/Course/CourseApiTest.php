<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Tests\LegacyWebService\Course;

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

    public function testGetCourses()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/courses_by_organization_response.xml')),
        ]);

        $courses = $this->api->Course()->getCourses();
        $this->assertSame(34, count($courses));
        $course = $courses[0];
        $this->assertSame('241333', $course->getIdentifier());
        $this->assertSame('Technische Informatik 1', $course->getName());
        $this->assertSame('german', $course->getLanguage());
        $this->assertSame('448001', $course->getCode());
        $this->assertSame('VO', $course->getType());
        $this->assertSame('', $course->getDescription());
        $this->assertSame(2.0, $course->getNumberOfCredits());
    }

    public function testGetCourses500()
    {
        $this->mockResponses([
            new Response(500, ['Content-Type' => 'text/xml;charset=utf-8'], ''),
        ]);

        $this->expectException(ApiException::class);
        $this->api->Course()->getCourses();
    }

    public function testGetCoursesInvalidXML()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/course_response_invalid_xml.xml')),
        ]);

        $this->expectException(ApiException::class);
        $this->api->Course()->getCourses();
    }

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

    public function testGetCourseByIdNotFound()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/course_by_id_response.xml')),
        ]);

        $this->expectException(ApiException::class);
        $this->expectExceptionCode(404);
        $this->api->Course()->getCourseById('123');
    }

    public function testGetCourseById500()
    {
        $this->mockResponses([
            new Response(500, ['Content-Type' => 'text/xml;charset=utf-8'], ''),
        ]);

        $this->expectException(ApiException::class);
        $this->api->Course()->getCourseById('123');
    }

    public function testGetCoursesByOrganization()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/courses_by_organization_response.xml')),
        ]);

        $courses = $this->api->Course()->getCoursesByOrganization('abc');

        $this->assertSame(34, count($courses));
        $course = $courses[0];
        $this->assertSame('241333', $course->getIdentifier());
        $this->assertSame('Technische Informatik 1', $course->getName());
        $this->assertSame('german', $course->getLanguage());
        $this->assertSame('448001', $course->getCode());
        $this->assertSame('VO', $course->getType());
        $this->assertSame('', $course->getDescription());
        $this->assertSame(2.0, $course->getNumberOfCredits());
    }
}
