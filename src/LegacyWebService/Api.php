<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService;

use Dbp\CampusonlineApi\LegacyWebService\Course\CourseApi;
use Dbp\CampusonlineApi\LegacyWebService\Organization\OrganizationUnitApi;
use Dbp\CampusonlineApi\LegacyWebService\Person\PersonApi;
use Dbp\CampusonlineApi\LegacyWebService\Room\RoomApi;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class Api implements LoggerAwareInterface
{
    public const HTTP_STATUS_NOT_FOUND = 404;
    public const LANGUAGE_PARAMETER_NAME = 'lang';

    /** @var Connection */
    private $connection;

    /** @var string */
    private $rootOrgUnitId;

    /** @var CacheItemPoolInterface|null */
    private $cache;

    /** @var int */
    private $cacheTtl;

    public function __construct(string $baseUrl, string $accessToken, string $rootOrgUnitId = '',
        LoggerInterface $logger = null, CacheItemPoolInterface $cache = null, int $cacheTTL = 0, $clientHandler = null)
    {
        $this->connection = new Connection($baseUrl, $accessToken);
        $this->rootOrgUnitId = $rootOrgUnitId;
        $this->cache = $cache;
        $this->cacheTtl = $cacheTTL;

        if ($logger !== null) {
            $this->connection->setLogger($logger);
        }
        if ($clientHandler !== null) {
            $this->connection->setClientHandler($clientHandler);
        }
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->connection->setLogger($logger);
    }

    public function setClientHandler(?object $handler)
    {
        $this->connection->setClientHandler($handler);
    }

    public function Room(): RoomApi
    {
        $roomApi = new RoomApi($this->connection, $this->rootOrgUnitId);
        if ($this->cache !== null) {
            $roomApi->setCache($this->cache, $this->cacheTtl);
        }

        return $roomApi;
    }

    public function OrganizationUnit(): OrganizationUnitApi
    {
        $orgUnitApi = new OrganizationUnitApi($this->connection, $this->rootOrgUnitId);
        if ($this->cache !== null) {
            $orgUnitApi->setCache($this->cache, $this->cacheTtl);
        }

        return $orgUnitApi;
    }

    public function Course(): CourseApi
    {
        $courseApi = new CourseApi($this->connection, $this->rootOrgUnitId);
        if ($this->cache !== null) {
            $courseApi->setCache($this->cache, $this->cacheTtl);
        }

        return $courseApi;
    }

    public function Person(): PersonApi
    {
        $personApi = new PersonApi($this->connection, $this->rootOrgUnitId);
        if ($this->cache !== null) {
            $personApi->setCache($this->cache, $this->cacheTtl);
        }

        return $personApi;
    }
}
