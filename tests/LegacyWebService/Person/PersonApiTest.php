<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Tests\LegacyWebService\Person;

use Dbp\CampusonlineApi\Helpers\FullPaginator;
use Dbp\CampusonlineApi\Helpers\PartialPaginator;
use Dbp\CampusonlineApi\LegacyWebService\Api;
use Dbp\CampusonlineApi\LegacyWebService\ApiException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class PersonApiTest extends TestCase
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

    public function testCheckConnection()
    {
        $this->mockResponses([
            new Response(400, ['Content-Type' => 'text/xml;charset=utf-8'], ''),
            new Response(404, ['Content-Type' => 'text/xml;charset=utf-8'], ''),
        ]);

        $this->api->Person()->checkConnection();
        $this->assertTrue(true);
    }

    /**
     * @throws ApiException
     */
    public function testGetStudentsByCourse()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/co_students_by_course_response.xml')),
        ]);

        $paginator = $this->api->Person()->getStudentsByCourse('276525', ['partialPagination' => false]);
        $this->assertInstanceOf(FullPaginator::class, $paginator);
        $this->assertSame(3, $paginator->getTotalNumItems());
        $this->assertSame(3, $paginator->getMaxNumItemsPerPage());
        $this->assertSame(1, $paginator->getCurrentPageNumber());

        $students = $paginator->getItems();
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

        $paginator = $this->api->Person()->getStudentsByCourse('276525', ['partialPagination' => false, 'perPage' => 1, 'page' => 2]);
        $this->assertInstanceOf(FullPaginator::class, $paginator);
        $this->assertSame(3, $paginator->getTotalNumItems());
        $this->assertSame(1, $paginator->getMaxNumItemsPerPage());
        $this->assertSame(2, $paginator->getCurrentPageNumber());

        $students = $paginator->getItems();
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

        $paginator = $this->api->Person()->getStudentsByCourse('276525', ['partialPagination' => true, 'perPage' => 2, 'page' => 2]);
        $this->assertInstanceOf(PartialPaginator::class, $paginator);
        $this->assertSame(2, $paginator->getMaxNumItemsPerPage());
        $this->assertSame(2, $paginator->getCurrentPageNumber());

        $students = $paginator->getItems();
        $this->assertCount(1, $students);

        $student = $students[0];
        $this->assertSame('3C', $student->getIdentifier());
        $this->assertSame('Doe', $student->getFamilyName());
        $this->assertSame('John', $student->getGivenName());
        $this->assertSame('john.doe@student.tugraz.at', $student->getEmail());
    }
}
