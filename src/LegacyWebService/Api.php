<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Rest;

use Dbp\CampusonlineApi\Rest\Student\StudentApi;
use Dbp\CampusonlineApi\Rest\UCard\UCardApi;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class Api implements LoggerAwareInterface
{
    private $connection;

    public function __construct($baseUrl, $clientId, $clientSecret)
    {
        $this->connection = new Connection($baseUrl, $clientId, $clientSecret);
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->connection->setLogger($logger);
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    public function addDataServiceOverride(string $dataServiceId, string $overrideId): void
    {
        $this->connection->addDataServiceOverride($dataServiceId, $overrideId);
    }

    public function Student(): StudentApi
    {
        return new StudentApi($this->connection);
    }

    public function UCard(): UCardApi
    {
        return new UCardApi($this->connection);
    }
}
