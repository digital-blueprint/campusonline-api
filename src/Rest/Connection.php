<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Rest;

use Dbp\CampusonlineApi\Helpers\ApiException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Connection implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $baseUrl;
    private $clientId;
    private $clientSecret;
    private $clientHandler;

    private $token;
    private $dataServices;

    public function __construct(string $baseUrl, string $clientId, string $clientSecret)
    {
        $this->baseUrl = $baseUrl;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->dataServices = [];
    }

    public function addDataServiceOverride(string $dataServiceId, string $overrideId): void
    {
        $this->dataServices[$dataServiceId] = $overrideId;
    }

    public function getDataServiceId(string $id): string
    {
        return $this->dataServices[$id] ?? $id;
    }

    public function setClientHandler(?object $handler): void
    {
        $this->clientHandler = $handler;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getClient(): Client
    {
        $token = $this->getToken();

        $stack = HandlerStack::create($this->clientHandler);
        $base_uri = $this->baseUrl;
        if (substr($base_uri, -1) !== '/') {
            $base_uri .= '/';
        }

        $client_options = [
            'base_uri' => $base_uri,
            'handler' => $stack,
            'headers' => [
                'Authorization' => 'Bearer '.$token,
                'Accept' => 'application/json',
            ],
        ];

        if ($this->logger !== null) {
            $stack->push(Tools::createLoggerMiddleware($this->logger));
        }

        $client = new Client($client_options);

        return $client;
    }

    private function getToken(): string
    {
        if ($this->token === null) {
            $this->refreshToken();
        }

        return $this->token;
    }

    /**
     * @throws ApiException
     */
    private function refreshToken(): void
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
            $response = $client->post($this->baseUrl.'/wbOAuth2.token', [
                'form_params' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type' => 'client_credentials',
                ],
            ]);
        } catch (GuzzleException $guzzleException) {
            throw ApiException::fromGuzzleException($guzzleException);
        }
        $data = $response->getBody()->getContents();

        try {
            $token = Tools::decodeJSON($data, true);
        } catch (\JsonException $exception) {
            throw new ApiException($exception->getMessage());
        }
        $this->token = $token['access_token'];
    }
}
