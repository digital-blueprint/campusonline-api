<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractApi implements LoggerAwareInterface
{
    protected const OFFSET_QUERY_PARAMETER_NAME = 'offset';
    protected const LIMIT_QUERY_PARAMETER_NAME = 'limit';
    protected const CURSOR_QUERY_PARAMETER_NAME = 'cursor';

    protected static function getOffsetBasedPaginationQueryParameters(?int $offset, ?int $limit): array
    {
        $queryParameters = [];
        if ($offset !== null) {
            $queryParameters[self::OFFSET_QUERY_PARAMETER_NAME] = $offset;
        }
        if ($limit !== null) {
            $queryParameters[self::LIMIT_QUERY_PARAMETER_NAME] = $limit;
        }

        return $queryParameters;
    }

    protected static function getCursorBasedPaginationQueryParameters(?string $cursor, ?int $limit): array
    {
        $queryParameters = [];
        if ($cursor !== null) {
            $queryParameters[self::CURSOR_QUERY_PARAMETER_NAME] = $cursor;
        }
        if ($limit !== null) {
            $queryParameters[self::LIMIT_QUERY_PARAMETER_NAME] = $limit;
        }

        return $queryParameters;
    }

    protected Connection $connection;

    public function __construct(string $baseUrl, string $clientId, string $clientSecret)
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

    public function getConnection(): Connection
    {
        return $this->connection;
    }
}
