<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PuRSApi;

use Dbp\CampusonlineApi\Helpers\ApiException;
use Dbp\CampusonlineApi\PublicRestApi\Connection;
use Dbp\CampusonlineApi\Rest\Tools;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

readonly class Api
{
    public function __construct(
        private Connection $connection,
        private string $customExportApiPath)
    {
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->connection->setLogger($logger);
    }

    public function setClientHandler(?object $handler): void
    {
        $this->connection->setClientHandler($handler);
    }

    public function getResources(array $queryParameters = []): array
    {
        try {
            return Tools::decodeJsonResponse(
                $this->connection->getClient()->get($this->customExportApiPath.'?'.http_build_query($queryParameters)));
        } catch (GuzzleException $guzzleException) {
            throw ApiException::fromGuzzleException($guzzleException);
        }
    }
}
