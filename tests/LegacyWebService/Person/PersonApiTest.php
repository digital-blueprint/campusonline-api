<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Tests\LegacyWebService\Person;

use Dbp\CampusonlineApi\Helpers\Page;
use Dbp\CampusonlineApi\LegacyWebService\Api;
use Dbp\CampusonlineApi\LegacyWebService\ApiException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class PersonApiTest extends TestCase
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

        $this->api->Person()->checkConnection();
        $this->expectNotToPerformAssertions();
    }

    /**
     * @throws ApiException
     */
    public function testGetStudentsByCourse()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/co_students_by_course_response.xml')),
        ]);

        $page = $this->api->Person()->getStudentsByCourse('276525');
        $this->assertInstanceOf(Page::class, $page);
        $this->assertSame(3, $page->getMaxNumItemsPerPage());
        $this->assertSame(1, $page->getCurrentPageNumber());

        $students = $page->getItems();
        $this->assertCount(3, $students);

        $student = $students[0];
        $this->assertSame('1A', $student->getIdentifier());
        $this->assertSame('Hill', $student->getFamilyName());
        $this->assertSame('Terrence', $student->getGivenName());
        $this->assertSame('terrence.hill@student.tugraz.at', $student->getEmail());
    }

    /**
     * @throws ApiException
     */
    public function testGetStudentsByCoursePagination()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/co_students_by_course_response.xml')),
        ]);

        $page = $this->api->Person()->getStudentsByCourse('276525', ['perPage' => 1, 'page' => 2]);
        $this->assertInstanceOf(Page::class, $page);
        $this->assertSame(1, $page->getMaxNumItemsPerPage());
        $this->assertSame(2, $page->getCurrentPageNumber());

        $students = $page->getItems();
        $this->assertCount(1, $students);

        $student = $students[0];
        $this->assertSame('2B', $student->getIdentifier());
        $this->assertSame('Baker', $student->getFamilyName());
        $this->assertSame('Josephine', $student->getGivenName());
        $this->assertSame('josephine.baker@student.tugraz.at', $student->getEmail());
    }

    /**
     * @throws ApiException
     */
    public function testGetStudentsByCoursePartialPagination()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/co_students_by_course_response.xml')),
        ]);

        $page = $this->api->Person()->getStudentsByCourse('276525', ['perPage' => 2, 'page' => 2]);
        $this->assertInstanceOf(Page::class, $page);
        $this->assertSame(2, $page->getMaxNumItemsPerPage());
        $this->assertSame(2, $page->getCurrentPageNumber());

        $students = $page->getItems();
        $this->assertCount(1, $students);

        $student = $students[0];
        $this->assertSame('3C', $student->getIdentifier());
        $this->assertSame('Doe', $student->getFamilyName());
        $this->assertSame('John', $student->getGivenName());
        $this->assertSame('john.doe@student.tugraz.at', $student->getEmail());
    }
}
