<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PuRSApi;

use Dbp\CampusonlineApi\Helpers\ApiException;
use Dbp\CampusonlineApi\PublicRestApi\Connection;
use Dbp\CampusonlineApi\Rest\Tools;
use GuzzleHttp\Exception\GuzzleException;

readonly class Api
{
    public function __construct(
        private Connection $connection,
        private string $customExportApiPath)
    {
    }

    public function getResources(array $queryParameters = []): iterable
    {
        try {
            return Tools::decodeJsonResponse(
                $this->connection->getClient()->get($this->customExportApiPath.'?'.http_build_query($queryParameters)));
        } catch (GuzzleException $guzzleException) {
            throw ApiException::fromGuzzleException($guzzleException);
        }
    }
}
