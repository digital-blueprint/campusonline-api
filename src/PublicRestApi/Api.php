<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

abstract class Api implements LoggerAwareInterface
{
    protected const FIRST_ITEM_INDEX_QUERY_PARAMETER_NAME = 'offset';
    protected const MAX_NUM_ITEMS_QUERY_PARAMETER_NAME = 'limit';

    protected static function getPaginationQueryParameters(int $firstItemIndex, int $maxNumItems): array
    {
        return [
            self::FIRST_ITEM_INDEX_QUERY_PARAMETER_NAME => $firstItemIndex,
            self::MAX_NUM_ITEMS_QUERY_PARAMETER_NAME => $maxNumItems,
        ];
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
