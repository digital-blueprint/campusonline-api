<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\DataServiceApi;

use Dbp\CampusonlineApi\Helpers\ApiException;
use Dbp\CampusonlineApi\Helpers\Tools;
use GuzzleHttp\Exception\GuzzleException;
use League\Uri\UriTemplate;
use Psr\Http\Message\ResponseInterface;

/**
 * A generic API wrapper for custom data services.
 */
abstract readonly class AbstractDataServiceApi
{
    public function __construct(
        protected Connection $connection,
        protected string $dataService)
    {
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * @return ApiResource[]
     *
     * @throws ApiException
     */
    protected function get(string $urlParameterFormat, array $urlParameters, array $options = []): array
    {
        $variables = $urlParameters;
        $variables['service'] = $this->connection->getDataServiceId($this->dataService);

        $uri = (string) (new UriTemplate('pl/rest/{service}/'.$urlParameterFormat))
            ->expand($variables);

        $client = $this->connection->getClient();
        try {
            $response = $client->get($uri, $options);
        } catch (GuzzleException $guzzleException) {
            throw ApiException::fromGuzzleException($guzzleException);
        }

        return $this->parseResourceList($response);
    }

    /**
     * @return ApiResource[]
     *
     * @throws ApiException
     */
    protected function parseResourceList(ResponseInterface $response): array
    {
        $resourceData = Tools::decodeJSON((string) $response->getBody(), true);

        $resultList = [];
        foreach ($resourceData['resource'] as $resource) {
            $content = null;
            $type = null;
            foreach ($resource['content'] as $key => $value) {
                if ($key === 'type') {
                    $type = $value;
                } elseif (is_array($value)) {
                    $content = $value;
                }
            }
            if ($content === null) {
                throw new \RuntimeException('content missing');
            }
            $resultList[] = new ApiResource($type, $content);
        }

        return $resultList;
    }
}
