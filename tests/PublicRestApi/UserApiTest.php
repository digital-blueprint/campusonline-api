<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Tests\PublicRestApi;

use Dbp\CampusonlineApi\PublicRestApi\Accounts\UserApi;
use Dbp\CampusonlineApi\PublicRestApi\Accounts\UserResource;
use Dbp\CampusonlineApi\PublicRestApi\Connection;
use Dbp\CampusonlineApi\PublicRestApi\CursorBasedResourcePage;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class UserApiTest extends TestCase
{
    private $api;

    public const RESPONSE = <<<JSON
        {
          "items": [
            {
              "personUid": "A3F8B2C9E1D74F6A",
              "accounts": [
                {
                  "accountStatusKey": "ACTIVE",
                  "accountTypeKey": "SOME",
                  "email": "john.doe@example.com",
                  "username": "jdoe"
                }
              ]
            }
          ],
          "limit": 100,
          "nextCursor": "xxxxxxxx=="
        }
        JSON;

    protected function setUp(): void
    {
        parent::setUp();
        $connection = new Connection('http://invalid', 'clientid', 'secret');
        $connection->setToken('nope', (new \DateTimeImmutable())->add(new \DateInterval('P1D')));
        $this->api = new UserApi($connection);
        $this->mockResponses([]);
    }

    private function mockResponses(array $responses): void
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $this->api->setClientHandler($stack);
    }

    public function testGetUserByPersonUid(): void
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], self::RESPONSE),
        ]);
        $result = $this->api->getUserByPersonUid('A3F8B2C9E1D74F6A');
        $this->assertSame('A3F8B2C9E1D74F6A', $result->getPersonUid());
        $this->assertSame(1, $result->getNumAccounts());
        $this->assertSame('ACTIVE', $result->getAccountStatusKey(0));
        $this->assertSame('SOME', $result->getAccountTypeKey(0));
        $this->assertSame('john.doe@example.com', $result->getEmail(0));
        $this->assertSame('jdoe', $result->getUsername(0));
    }

    public function testGetUsersCursorBased(): void
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], self::RESPONSE),
        ]);
        $result = $this->api->getUsersCursorBased();
        $this->assertInstanceOf(CursorBasedResourcePage::class, $result);
        $resources = iterator_to_array($result->getResources());
        $this->assertCount(1, $resources);
        $userResource = $resources[0];
        assert($userResource instanceof UserResource);
        $this->assertSame('A3F8B2C9E1D74F6A', $userResource->getPersonUid());
        $this->assertSame('ACTIVE', $userResource->getAccountStatusKey(0));
        $this->assertSame('SOME', $userResource->getAccountTypeKey(0));
        $this->assertSame('john.doe@example.com', $userResource->getEmail(0));
        $this->assertSame('jdoe', $userResource->getUsername(0));
        $this->assertSame('xxxxxxxx==', $result->getNextCursor());
    }

    public function testGetUsersOffsetBased(): void
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], self::RESPONSE),
        ]);
        $result = $this->api->getUsersOffsetBased();
        $resources = iterator_to_array($result);
        $this->assertCount(1, $resources);
        $userResource = $resources[0];
        assert($userResource instanceof UserResource);
        $this->assertSame('A3F8B2C9E1D74F6A', $userResource->getPersonUid());
        $this->assertSame('ACTIVE', $userResource->getAccountStatusKey(0));
        $this->assertSame('SOME', $userResource->getAccountTypeKey(0));
        $this->assertSame('john.doe@example.com', $userResource->getEmail(0));
        $this->assertSame('jdoe', $userResource->getUsername(0));
    }
}
