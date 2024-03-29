<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Rest;

use Dbp\CampusonlineApi\Helpers\ApiException;
use Dbp\CampusonlineApi\Rest\Generic\GenericApi;
use Dbp\CampusonlineApi\Rest\ResearchProject\ResearchProjectApi;
use Dbp\CampusonlineApi\Rest\Student\StudentApi;
use Dbp\CampusonlineApi\Rest\UCard\UCardApi;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class Api implements LoggerAwareInterface
{
    /**
     * @deprecated Use FilterBuilder instead
     */
    public const EQUALS_FILTER_OPERATOR = 1;

    /**
     * @deprecated Use FilterBuilder instead
     */
    public const LIKE_CASE_INSENSITIVE_FILTER_OPERATOR = 2;

    /** @var Connection */
    private $connection;

    public function __construct($baseUrl, $clientId, $clientSecret)
    {
        $this->connection = new Connection($baseUrl, $clientId, $clientSecret);
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->connection->setLogger($logger);
    }

    public function setClientHandler(?object $handler)
    {
        $this->connection->setClientHandler($handler);
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

    public function ResearchProject(): ResearchProjectApi
    {
        return new ResearchProjectApi($this->connection);
    }

    /**
     * @param string $dataService The name of the exported data service. In case the endpoint is located at something
     *                            like `pl/rest/loc_apiMyExport` then the data service name is `loc_apiMyExport`
     */
    public function Generic(string $dataService): GenericApi
    {
        return new GenericApi($this->connection, $dataService);
    }

    /**
     * @deprecated Use FilterBuilder instead
     */
    public static function getFilter(string $filterName, int $operator, $filterValue): string
    {
        switch ($operator) {
            case self::EQUALS_FILTER_OPERATOR:
                $operatorString = '-eq=';
                break;
            case self::LIKE_CASE_INSENSITIVE_FILTER_OPERATOR:
                $operatorString = '-likeI=';
                break;
            default:
                throw new ApiException('unknown filter operator '.$operator);
        }

        return Tools::validateFilterName($filterName).$operatorString.Tools::validateFilterValue($filterValue);
    }

    /**
     * Check if the API responds with the given HTTP response code.
     * Useful for checkConnection().
     */
    public static function expectApiException(callable $apiCall, int $expectedHttpResponseCode): void
    {
        try {
            $apiCall();
        } catch (ApiException $e) {
            if ($e->isHttpResponseCode() && $e->getCode() === $expectedHttpResponseCode) {
                return;
            }
        }
        throw new \RuntimeException("Didn't respond with $expectedHttpResponseCode as expected");
    }
}
