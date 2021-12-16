<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\API;

use Dbp\CampusonlineApi\Student\StudentAPI;
use Dbp\CampusonlineApi\UCard\UCardAPI;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class API implements LoggerAwareInterface
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
        return new StudentAPI($this->connection);
    }

    public function UCard(): UCardAPI
    {
        return new UCardAPI($this->connection);
    }
}
