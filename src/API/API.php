<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\API;

use Dbp\CampusonlineApi\Student\StudentAPI;
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

    public function setClientHandler(?object $handler): void
    {
        $this->connection->setClientHandler($handler);
    }

    public function setToken(string $token): void
    {
        $this->connection->setToken($token);
    }

    public function addDataServiceOverride(string $dataServiceId, string $overrideId): void
    {
        $this->connection->addDataServiceOverride($dataServiceId, $overrideId);
    }

    public function getStudent(): StudentApi
    {
        return new StudentAPI($this->connection);
    }
}
