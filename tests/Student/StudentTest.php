<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Tests\Student;

use Dbp\CampusonlineApi\API\API;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class StudentTest extends TestCase
{
    private $api;

    protected function setUp(): void
    {
        $this->api = new API('http://localhost', 'nope', 'nope');
        $this->api->getConnection()->setToken('foo');
        $this->mockResponses([]);
    }

    private function mockResponses(array $responses)
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $this->api->getConnection()->setClientHandler($stack);
    }

    public function testGetForIdent()
    {
        $GET_RESPONSE = file_get_contents(__DIR__.'/studentdata-response.json');
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], $GET_RESPONSE),
        ]);
        $student = $this->api->Student();
        $list = $student->getStudentDataByPersonId('12345');
        $this->assertCount(1, $list);
        $data = $list[0];

        $this->assertSame('Max', $data->firstName);
        $this->assertSame('Mustermann', $data->lastName);
        $this->assertSame('-12345', $data->identId);
        $this->assertSame('44D9DBB60B6C2C24', $data->identIdObfuscated);
        $this->assertSame('12345', $data->personId);
    }
}
