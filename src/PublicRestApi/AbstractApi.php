<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi;

use Dbp\CampusonlineApi\Helpers\ApiException;
use Dbp\CampusonlineApi\Rest\Tools;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractApi implements LoggerAwareInterface
{
    protected const OFFSET_QUERY_PARAMETER_NAME = 'offset';
    protected const LIMIT_QUERY_PARAMETER_NAME = 'limit';
    protected const CURSOR_QUERY_PARAMETER_NAME = 'cursor';

    public function __construct(private readonly Connection $connection)
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

    public function getConnection(): Connection
    {
        return $this->connection;
    }

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

    protected function getClient(): Client
    {
        return $this->connection->getClient();
    }

    protected function getResourceByIdentifier(string $apiPath, string $resourceClassName, string $identifier): Resource
    {
        try {
            $responseData = Tools::decodeJsonResponse(
                $this->getClient()->get(
                    $apiPath.'/'.rawurlencode($identifier)));

            return new $resourceClassName($responseData);
        } catch (GuzzleException $guzzleException) {
            throw ApiException::fromGuzzleException($guzzleException);
        }
    }

    protected function getResourcesCursorBased(string $apiPath, string $resourceClassName, array $queryParameters = [],
        ?string $cursor = null, int $maxNumItems = 30): CursorBasedResourcePage
    {
        try {
            // WORKAROUND: CO ignores limit=0
            if ($maxNumItems === 0) {
                return CursorBasedResourcePage::createEmptyPage($cursor);
            }
            $queryParameters = array_merge(
                $queryParameters,
                self::getCursorBasedPaginationQueryParameters($cursor, $maxNumItems)
            );
            $responseData = Tools::decodeJsonResponse($this->getClient()->get(
                $apiPath.'?'.self::buildQueryString($queryParameters)));

            return CursorBasedResourcePage::createFromResponseData($responseData, $resourceClassName);
        } catch (GuzzleException $guzzleException) {
            throw ApiException::fromGuzzleException($guzzleException);
        }
    }

    protected function getResourcesOffsetBased(string $apiPath, string $resourceClassName, array $queryParameters = [],
        int $firstItemIndex = 0, int $maxNumItems = 30): iterable
    {
        return $this->getResources($apiPath, $resourceClassName,
            array_merge(
                $queryParameters,
                self::getOffsetBasedPaginationQueryParameters($firstItemIndex, $maxNumItems)
            ));
    }

    protected function getResources(string $apiPath, string $resourceClassName, array $queryParameters = []): iterable
    {
        try {
            return self::createResourceIterator(
                $this->getClient()->get($apiPath.'?'.self::buildQueryString($queryParameters)),
                $resourceClassName);
        } catch (GuzzleException $guzzleException) {
            throw ApiException::fromGuzzleException($guzzleException);
        }
    }

    protected function getResourceByIdentifierFromCollection(string $identifier, string $identifierQueryParameterName,
        string $apiPath, string $resourceClassName, array $queryParameters = []): Resource
    {
        $resources = iterator_to_array(
            $this->getResources(
                $apiPath,
                $resourceClassName,
                array_merge($queryParameters, [
                    $identifierQueryParameterName => $identifier,
                ])
            )
        );

        $resource = $resources[0] ?? null;
        if ($resource === null) {
            throw new ApiException('Item not found', ApiException::HTTP_NOT_FOUND, true);
        }

        return $resource;
    }

    private static function createResourceIterator(ResponseInterface $response, string $resourceClassName): iterable
    {
        foreach (Tools::decodeJsonResponse($response)['items'] ?? [] as $organizationResourceData) {
            yield new $resourceClassName($organizationResourceData);
        }
    }

    private static function buildQueryString(array $queryParameters): string
    {
        $queryParts = [];
        foreach ($queryParameters as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $item) {
                    $queryParts[] = urlencode($key).'='.urlencode((string) $item);
                }
            } else {
                $queryParts[] = urlencode($key).'='.urlencode((string) $value);
            }
        }

        return implode('&', $queryParts);
    }
}
