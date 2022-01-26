<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService;

use Dbp\CampusonlineApi\LegacyWebService\Room\RoomApi;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class Api implements LoggerAwareInterface
{
    public const LANGUAGE_PARAMETER_NAME = 'lang';
    public const LANGUAGE_EN = 'en';
    public const DEFAULT_LANGUAGE = self::LANGUAGE_EN;

    private $connection;
    private $rootOrgUnitId;

    public function __construct($baseUrl, $accessToken, $rootOrgUnitId = 0)
    {
        $this->connection = new Connection($baseUrl, $accessToken);
        $this->rootOrgUnitId = $rootOrgUnitId;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->connection->setLogger($logger);
    }

    public function setCache(?CacheItemPoolInterface $cachePool, int $ttl)
    {
        $this->connection->setCache($cachePool, $ttl);
    }

    public function setClientHandler(?object $handler)
    {
        $this->connection->setClientHandler($handler);
    }

    public function Room(): RoomApi
    {
        return new RoomApi($this->connection, $this->rootOrgUnitId);
    }
}
