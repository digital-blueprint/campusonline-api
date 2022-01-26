<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService;

use Dbp\CampusonlineApi\Rest\ApiException;
use Dbp\CampusonlineApi\Rest\Tools;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Storage\Psr6CacheStorage;
use Kevinrob\GuzzleCache\Strategy\GreedyCacheStrategy;
use League\Uri\Contracts\UriException;
use League\Uri\UriTemplate;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Connection implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const ACCESS_TOKEN_PARAMETER_NAME = 'token';

    private $cachePool;
    private $cacheTTL;
    private $baseUrl;
    private $accessToken;
    private $clientHandler;

    public function __construct(string $baseUrl, string $accessToken)
    {
        $this->clientHandler = null;
        $this->logger = null;
        $this->baseUrl = $baseUrl;
        $this->accessToken = $accessToken;
        $this->cachePool = null;
        $this->cacheTTL = 0;
    }

    public function setCache(?CacheItemPoolInterface $cachePool, int $ttl)
    {
        $this->cachePool = $cachePool;
        $this->cacheTTL = $ttl;
    }

    public function setClientHandler(?object $handler)
    {
        $this->clientHandler = $handler;
    }

    /**
     * @param array $parameters Array of <param name> - <param value> pairs
     *
     * @throws ApiException
     */
    public function get(string $uri, array $parameters = []): string
    {
        try {
            $uri = $this->makeUri($uri, $parameters);
        } catch (UriException $e) {
            throw new ApiException('invalid uri or parameters: '.$uri);
        }

        $client = $this->getClient();
        try {
            $response = $client->get($uri);
        } catch (GuzzleException $e) {
            throw self::createApiException($e);
        }

        return (string) $response->getBody();
    }

    private function getClient(): Client
    {
        $base_uri = $this->baseUrl;
        if (substr($base_uri, -1) !== '/') {
            $base_uri .= '/';
        }

        $stack = HandlerStack::create($this->clientHandler);
        if ($this->logger !== null) {
            $stack->push(Tools::createLoggerMiddleware($this->logger));
        }

        if ($this->cachePool !== null) {
            $cacheMiddleWare = new CacheMiddleware(
                new GreedyCacheStrategy(
                    new Psr6CacheStorage($this->cachePool),
                    $this->cacheTTL
                )
            );
            $cacheMiddleWare->setHttpMethods(['GET' => true, 'HEAD' => true]);
            $stack->push($cacheMiddleWare);
        }

        $client_options = [
            'base_uri' => $base_uri,
            'handler' => $stack,
        ];

        return new Client($client_options);
    }

    /**
     * @throws UriException
     */
    private function makeUri($uri, $parameters): string
    {
        $parameters[self::ACCESS_TOKEN_PARAMETER_NAME] = $this->accessToken;

        $uri = $uri.'?';
        foreach ($parameters as $param_key => $param_value) {
            if ($param_key !== array_key_first($parameters)) {
                $uri .= '&';
            }
            $uri .= $param_key.'={'.$param_key.'}';
        }

        $uriTemplate = new UriTemplate($uri);

        return (string) $uriTemplate->expand($parameters);
    }

    private static function createApiException(GuzzleException $e): ApiException
    {
        if ($e instanceof RequestException) {
            $response = $e->getResponse();
            if ($response === null) {
                return new ApiException('Unknown error');
            }

            return new ApiException($e->getMessage(), $response->getStatusCode());
        } else {
            return new ApiException($e->getMessage());
        }
    }
}
