<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi;

use Dbp\CampusonlineApi\Helpers\ApiException;
use Dbp\CampusonlineApi\Rest\Tools;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Connection implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ?object $clientHandler = null;

    private ?string $token = null;

    public function __construct(
        private string $baseUrl,
        private readonly string $clientId,
        private readonly string $clientSecret)
    {
        if (false === str_ends_with($this->baseUrl, '/')) {
            $this->baseUrl .= '/';
        }
    }

    public function setClientHandler(?object $handler): void
    {
        $this->clientHandler = $handler;
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

    private function getToken(): string
    {
        return $this->token ?? $this->token = $this->getNewToken();
    }

    /**
     * @throws ApiException
     */
    private function getNewToken(): string
    {
        $stack = HandlerStack::create($this->clientHandler);
        $client_options = [
            'handler' => $stack,
        ];
        if ($this->logger !== null) {
            $stack->push(Tools::createLoggerMiddleware($this->logger));
        }
        $client = new Client($client_options);

        try {
            $authServerUrl = Tools::decodeJsonResponse(
                $client->get($this->baseUrl.'/co/public/api/environment'))['authServerUrl'] ?? null;
            if ($authServerUrl === null) {
                throw new ApiException('auth server url not found in environment');
            }

            $tokenEndpoint = Tools::decodeJsonResponse(
                $client->get($authServerUrl.'/.well-known/openid-configuration'))['token_endpoint'] ?? null;
            if ($tokenEndpoint === null) {
                throw new ApiException('token endpoint not found in auth server response');
            }

            $token = Tools::decodeJsonResponse(
                $client->post($tokenEndpoint, [
                    'form_params' => [
                        'client_id' => $this->clientId,
                        'client_secret' => $this->clientSecret,
                        'grant_type' => 'client_credentials',
                    ],
                ]))['access_token'] ?? null;
            if ($token === null) {
                throw new ApiException('access token not found in auth server response');
            }

            return $token;
        } catch (GuzzleException $guzzleException) {
            throw ApiException::fromGuzzleException($guzzleException);
        }
    }
}
