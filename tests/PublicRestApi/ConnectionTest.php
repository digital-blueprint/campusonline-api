<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Tests\PublicRestApi;

use Dbp\CampusonlineApi\PublicRestApi\Connection;
use Dbp\CampusonlineApi\Rest\Tools;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class ConnectionTest extends TestCase
{
    public function testReusesFetchedTokenFromCacheAcrossConnectionInstances(): void
    {
        $cacheItem = new InMemoryCacheItem(null, false);
        $cachePool = new InMemoryCachePool($cacheItem);

        $firstHandler = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'authServerUrl' => 'https://auth.example.invalid',
            ], JSON_THROW_ON_ERROR)),
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'token_endpoint' => 'https://auth.example.invalid/token',
            ], JSON_THROW_ON_ERROR)),
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'access_token' => 'shared-cache-token',
                'expires_in' => 120,
            ], JSON_THROW_ON_ERROR)),
        ]);

        $firstConnection = new Connection('https://api.example.invalid', 'client-id', 'secret');
        $firstConnection->setClientHandler($firstHandler);
        $firstConnection->setCache($cachePool, 999);

        $firstClient = $firstConnection->getClient();

        self::assertSame('Bearer shared-cache-token', $firstClient->getConfig('headers')['Authorization']);
        self::assertSame('shared-cache-token', $cacheItem->value);
        self::assertSame(90, $cacheItem->expiresAfterValue);
        self::assertSame(1, $cachePool->saveCalls);
        self::assertSame(Tools::escapeCacheKey('client-tokens/client-id'), $cachePool->lastKey);

        // After the first connection, the token has been stored in the shared cache.
        // Mark the fake cache item as a cache hit for the second connection.
        $cacheItem->hit = true;

        $secondConnection = new Connection('https://api.example.invalid', 'client-id', 'secret');
        $secondConnection->setCache($cachePool, 999);

        // If the second connection tries to fetch a token from the auth server,
        // this empty handler will fail. Therefore the token must come from cache.
        $secondConnection->setClientHandler(new MockHandler([]));

        $secondClient = $secondConnection->getClient();

        self::assertSame('Bearer shared-cache-token', $secondClient->getConfig('headers')['Authorization']);
        self::assertSame(3, $cachePool->getItemCalls);
        self::assertSame(1, $cachePool->saveCalls);
        self::assertSame(Tools::escapeCacheKey('client-tokens/client-id'), $cachePool->lastKey);
    }
}

final class InMemoryCacheItem implements CacheItemInterface
{
    public mixed $value;
    public bool $hit;
    public ?int $expiresAfterValue = null;

    public function __construct(mixed $value, bool $hit)
    {
        $this->value = $value;
        $this->hit = $hit;
    }

    public function getKey(): string
    {
        return 'token-key';
    }

    public function get(): mixed
    {
        return $this->value;
    }

    public function isHit(): bool
    {
        return $this->hit;
    }

    public function set(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function expiresAt(?\DateTimeInterface $expiration): static
    {
        return $this;
    }

    public function expiresAfter(int|\DateInterval|null $time): static
    {
        if (is_int($time)) {
            $this->expiresAfterValue = $time;
        }

        return $this;
    }
}
final class InMemoryCachePool implements CacheItemPoolInterface
{
    public int $getItemCalls = 0;
    public int $saveCalls = 0;
    public ?string $lastKey = null;

    public function __construct(private InMemoryCacheItem $item)
    {
    }

    public function getItem(string $key): CacheItemInterface
    {
        ++$this->getItemCalls;
        $this->lastKey = $key;

        return $this->item;
    }

    public function getItems(array $keys = []): iterable
    {
        return [$this->item];
    }

    public function hasItem(string $key): bool
    {
        return $this->item->isHit();
    }

    public function clear(): bool
    {
        return true;
    }

    public function deleteItem(string $key): bool
    {
        return true;
    }

    public function deleteItems(array $keys): bool
    {
        return true;
    }

    public function save(CacheItemInterface $item): bool
    {
        ++$this->saveCalls;

        return true;
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        return true;
    }

    public function commit(): bool
    {
        return true;
    }
}
