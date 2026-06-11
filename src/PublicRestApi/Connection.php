<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi;

use Dbp\CampusonlineApi\Helpers\ApiException;
use Dbp\CampusonlineApi\Rest\Tools;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Connection implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const CACHE_SUBNAMESPACE = 'DbpCampusonlineApiPublicRestApiConnection';
    public const DEFAULT_TOKEN_REFRESH_INTERVAL_SECS = self::DEFAULT_TOKEN_VALIDITY_SECS - self::REFRESH_TOKEN_SAFETY_MARGIN_SECS;

    private const DEFAULT_TOKEN_VALIDITY_SECS = 300;
    private const REFRESH_TOKEN_SAFETY_MARGIN_SECS = 30;

    private ?object $clientHandler = null;

    private ?string $token = null;
    private ?\DateTimeImmutable $requestNewTokenBefore = null;

    private ?CacheItemPoolInterface $cachePool = null;
    private int $fallbackCacheTTL = self::DEFAULT_TOKEN_REFRESH_INTERVAL_SECS;

    public function __construct(
        private string $baseUrl,
        private readonly string $clientId,
        private readonly string $clientSecret
    ) {
        if (false === str_ends_with($this->baseUrl, '/')) {
            $this->baseUrl .= '/';
        }
    }

    public function setClientHandler(?object $handler): void
    {
        $this->clientHandler = $handler;
    }

    public function setCache(
        ?CacheItemPoolInterface $cachePool,
        int $fallbackTTL = self::DEFAULT_TOKEN_REFRESH_INTERVAL_SECS
    ): void {
        $this->cachePool = $cachePool;
        $this->fallbackCacheTTL = $fallbackTTL;
    }

    public function getClient(): Client
    {
        $stack = HandlerStack::create($this->clientHandler);
        $clientOptions = [
            'base_uri' => $this->baseUrl,
            'handler' => $stack,
            'headers' => [
                'Authorization' => 'Bearer '.$this->getToken(),
                'Accept' => 'application/json',
            ],
        ];

        if ($this->logger !== null) {
            $stack->push(Tools::createLoggerMiddleware($this->logger));
        }

        return new Client($clientOptions);
    }

    public function setToken(string $token, \DateTimeImmutable $requestNewTokenBefore): void
    {
        $this->token = $token;
        $this->requestNewTokenBefore = $requestNewTokenBefore;
    }

    /**
     * @internal
     */
    public function getTokenForTesting(): string
    {
        return $this->getToken();
    }

    private function getTokenCacheKey(): string
    {
        return Tools::escapeCacheKey('client-tokens/'.$this->clientId);
    }

    /**
     * @throws ApiException
     */
    private function getToken(): string
    {
        if ($this->token !== null
            && $this->requestNewTokenBefore !== null
            && (new \DateTimeImmutable()) < $this->requestNewTokenBefore) {
            return $this->token;
        }

        $cachePool = $this->cachePool;
        $cacheItem = null;

        if ($cachePool !== null) {
            $cacheItem = $cachePool->getItem($this->getTokenCacheKey());

            if ($cacheItem->isHit()) {
                $cachedToken = $cacheItem->get();

                if (is_string($cachedToken) && $cachedToken !== '') {
                    $this->token = $cachedToken;

                    return $this->token;
                }
            }
        }

        $stack = HandlerStack::create($this->clientHandler);
        $clientOptions = [
            'handler' => $stack,
        ];

        if ($this->logger !== null) {
            $stack->push(Tools::createLoggerMiddleware($this->logger));
        }

        $client = new Client($clientOptions);

        try {
            $authServerUrl = Tools::decodeJsonResponse(
                $client->get($this->baseUrl.'/co/public/api/environment')
            )['authServerUrl'] ?? null;

            if ($authServerUrl === null) {
                throw new ApiException('auth server url not found in environment');
            }

            $tokenEndpoint = Tools::decodeJsonResponse(
                $client->get($authServerUrl.'/.well-known/openid-configuration')
            )['token_endpoint'] ?? null;

            if ($tokenEndpoint === null) {
                throw new ApiException('token endpoint not found in auth server response');
            }

            $tokenData = Tools::decodeJsonResponse(
                $client->post($tokenEndpoint, [
                    'form_params' => [
                        'client_id' => $this->clientId,
                        'client_secret' => $this->clientSecret,
                        'grant_type' => 'client_credentials',
                    ],
                ])
            );

            if (null === ($token = $tokenData['access_token'] ?? null)) {
                throw new ApiException('access token not found in auth server response');
            }

            $this->token = $token;

            if (null === ($expiresIn = $tokenData['expires_in'] ?? null)) {
                $getNewTokenInSecs = $this->fallbackCacheTTL;
            } else {
                $getNewTokenInSecs = max(0, (int) $expiresIn - self::REFRESH_TOKEN_SAFETY_MARGIN_SECS);
            }

            if ($getNewTokenInSecs > 0) {
                $this->requestNewTokenBefore = (new \DateTimeImmutable())
                    ->add(new \DateInterval('PT'.$getNewTokenInSecs.'S'));

                if ($cacheItem !== null) {
                    $cacheItem->set($this->token);
                    $cacheItem->expiresAfter($getNewTokenInSecs);
                    $cachePool->save($cacheItem);
                }
            }
        } catch (GuzzleException $guzzleException) {
            throw ApiException::fromGuzzleException($guzzleException);
        }

        return $this->token;
    }
}
