<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService;

use Dbp\CampusonlineApi\LegacyWebService\Course\CourseApi;
use Dbp\CampusonlineApi\LegacyWebService\Organization\OrganizationUnitApi;
use Dbp\CampusonlineApi\LegacyWebService\Room\RoomApi;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class Api implements LoggerAwareInterface
{
    public const LANGUAGE_PARAMETER_NAME = 'lang';

    private $connection;
    private $rootOrgUnitId;

    public function __construct($baseUrl, $accessToken, $rootOrgUnitId = 0,
                                $logger = null, $cache = null, $cacheTTL = 0, $clientHandler = null)
    {
        $this->rootOrgUnitId = $rootOrgUnitId;
        $this->connection = new Connection($baseUrl, $accessToken);

        if ($logger !== null) {
            $this->connection->setLogger($logger);
        }
        if ($cache !== null) {
            $this->connection->setCache($cache, $cacheTTL);
        }
        if ($clientHandler !== null) {
            $this->connection->setClientHandler($clientHandler);
        }
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

    public function OrganizationUnit(): OrganizationUnitApi
    {
        return new OrganizationUnitApi($this->connection, $this->rootOrgUnitId);
    }

    public function Course(): CourseApi
    {
        return new CourseApi($this->connection, $this->rootOrgUnitId);
    }
}
