<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Tests\PublicRestApi;

use Dbp\CampusonlineApi\PublicRestApi\Connection;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\Attribute\TimeSensitive;
use Symfony\Bridge\PhpUnit\ClockMock;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

#[TimeSensitive(ArrayAdapter::class)]
class ConnectionTest extends TestCase
{
    public function testTokenCacheItemPool(): void
    {
        ClockMock::withClockMock(true);

        $cachePool = new ArrayAdapter();
        $tokenExpiresIn = 120;
        $cacheItemExpiresIn = $tokenExpiresIn - 30;

        // Initially, the token must be requested from the auth server.
        $firstConnection = new Connection(
            'https://api.example.invalid',
            'client-id',
            'secret'
        );
        $mockHandler = new MockHandler(
            self::getMockAuthServerResponses(
                'shared-cache-token',
                $tokenExpiresIn
            )
        );
        $firstConnection->setClientHandler($mockHandler);
        $firstConnection->setCache($cachePool, 999);

        self::assertSame(
            'shared-cache-token',
            $firstConnection->getTokenForTesting()
        );
        self::assertSame(0, $mockHandler->count());

        // The cache item must still be valid, so no API request is required.
        ClockMock::sleep($cacheItemExpiresIn - 1);

        $secondConnection = new Connection(
            'https://api.example.invalid',
            'client-id',
            'secret'
        );
        $secondConnection->setCache($cachePool, 999);

        self::assertSame(
            'shared-cache-token',
            $secondConnection->getTokenForTesting()
        );

        // The cache item has expired, so a new token must be requested.
        ClockMock::sleep(2);

        $thirdConnection = new Connection(
            'https://api.example.invalid',
            'client-id',
            'secret'
        );
        $mockHandler = new MockHandler(
            self::getMockAuthServerResponses(
                'shared-cache-token',
                $tokenExpiresIn
            )
        );
        $thirdConnection->setClientHandler($mockHandler);
        $thirdConnection->setCache($cachePool, 999);

        self::assertSame(
            'shared-cache-token',
            $thirdConnection->getTokenForTesting()
        );
        self::assertSame(0, $mockHandler->count());
    }

    public function testTokenRequestCache(): void
    {
        // Initially, the token must be requested from the auth server.
        $connection = new Connection(
            'https://api.example.invalid',
            'client-id',
            'secret'
        );
        $mockHandler = new MockHandler(
            self::getMockAuthServerResponses(
                'shared-cache-token',
                120
            )
        );
        $connection->setClientHandler($mockHandler);

        self::assertSame(
            'shared-cache-token',
            $connection->getTokenForTesting()
        );
        self::assertSame(0, $mockHandler->count());

        // The token must now be reused from the in-memory request cache.
        self::assertSame(
            'shared-cache-token',
            $connection->getTokenForTesting()
        );
    }

    /**
     * @return Response[]
     */
    private static function getMockAuthServerResponses(
        string $token = 'token',
        int $expiresIn = 300
    ): array {
        return [
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode([
                    'authServerUrl' => 'https://auth.example.invalid',
                ], JSON_THROW_ON_ERROR)
            ),
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode([
                    'token_endpoint' => 'https://auth.example.invalid/token',
                ], JSON_THROW_ON_ERROR)
            ),
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode([
                    'access_token' => $token,
                    'expires_in' => $expiresIn,
                ], JSON_THROW_ON_ERROR)
            ),
        ];
    }
}
