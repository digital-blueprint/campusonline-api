<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Tests\Rest;

use Dbp\CampusonlineApi\Helpers\ApiException;
use Dbp\CampusonlineApi\Rest\Connection;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
    /**
     * @var Connection
     */
    private $conn;

    protected function setUp(): void
    {
        $this->conn = new Connection('http://localhost', 'nope', 'nope');
        $this->mockResponses([]);
    }

    private function mockResponses(array $responses)
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $this->conn->setClientHandler($stack);
    }

    public function testFetchToken()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], '{"access_token": "foobar"}'),
        ]);
        $this->conn->getClient();
        $this->assertTrue(true);
    }

    public function testFetchTokenNoAuth()
    {
        $this->mockResponses([
            new Response(401, ['Content-Type' => 'application/json'],
                '{"error":"invalid_client", "error_description":"Der Client ist nicht autorisiert.", "error_uri":""}'),
        ]);
        $this->expectException(ApiException::class);
        $this->conn->getClient();
    }
}
