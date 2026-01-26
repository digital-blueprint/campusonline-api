<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService;

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
    private const LANGUAGE_PARAMETER_NAME = 'language';
    private const LANGUAGE_EN = 'en';
    private const LANGUAGE_DE = 'de';
    private const DEFAULT_LANGUAGE = self::LANGUAGE_EN;

    private ?CacheItemPoolInterface $cachePool = null;
    private int $cacheTTL = 0;
    private string $baseUrl;
    private string $accessToken;
    private ?object $clientHandler = null;

    public function __construct(string $baseUrl, string $accessToken)
    {
        $this->baseUrl = $baseUrl;
        $this->accessToken = $accessToken;
    }

    public function setCache(?CacheItemPoolInterface $cachePool, int $ttl): void
    {
        $this->cachePool = $cachePool;
        $this->cacheTTL = $ttl;
    }

    public function setClientHandler(?object $handler): void
    {
        $this->clientHandler = $handler;
    }

    /**
     * @param array $uriParameters Array of <param name> - <param value> pairs
     *
     * @throws ApiException
     */
    public function get(string $uri, array $uriParameters = [], array $options = [], bool $cache = true): string
    {
        try {
            $uri = self::makeUri($uri, $uriParameters, $options, $this->accessToken);
        } catch (UriException $e) {
            throw new ApiException('invalid uri or parameters: '.$uri);
        }

        $client = $this->getClient($cache);
        try {
            $response = $client->get($uri);
        } catch (GuzzleException $e) {
            throw self::createApiException($e);
        }

        return (string) $response->getBody();
    }

    private function getClient(bool $cache = true): Client
    {
        $base_uri = $this->baseUrl;
        if (substr($base_uri, -1) !== '/') {
            $base_uri .= '/';
        }

        $stack = HandlerStack::create($this->clientHandler);
        if ($this->logger !== null) {
            $stack->push(Tools::createLoggerMiddleware($this->logger));
        }

        if ($this->cachePool !== null && $cache) {
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

    public static function getLanguageParameter(array $options): string
    {
        switch ($options[Api::LANGUAGE_PARAMETER_NAME] ?? '') {
            case self::LANGUAGE_EN:
                return self::LANGUAGE_EN;
            case self::LANGUAGE_DE:
                return self::LANGUAGE_DE;
            default:
                return self::DEFAULT_LANGUAGE;
        }
    }

    /**
     * @throws UriException
     */
    public static function makeUri(string $uri, array $uriParameters = [], array $options = [], string $accessToken = ''): string
    {
        $uriParameters[self::ACCESS_TOKEN_PARAMETER_NAME] = $accessToken;
        $uriParameters[self::LANGUAGE_PARAMETER_NAME] = self::getLanguageParameter($options);

        $uri = $uri.'?';
        foreach ($uriParameters as $param_key => $param_value) {
            if ($param_key !== array_key_first($uriParameters)) {
                $uri .= '&';
            }
            $uri .= $param_key.'={'.$param_key.'}';
        }

        $uriTemplate = new UriTemplate($uri);

        return (string) $uriTemplate->expand($uriParameters);
    }

    private static function hideToken(string $message): string
    {
        // hide token parameters
        return preg_replace('/([&?]token=)[\w\d-]+/i', '${1}hidden', $message);
    }

    private static function createApiException(GuzzleException $e): ApiException
    {
        if ($e instanceof RequestException) {
            $response = $e->getResponse();
            if ($response === null) {
                return new ApiException('Unknown error');
            }

            return new ApiException(self::hideToken($e->getMessage()), $e->getCode(), $e->getResponse() !== null);
        }

        return new ApiException(self::hideToken($e->getMessage()), $e->getCode(), false);
    }
}
