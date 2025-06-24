<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Organizations;

use Dbp\CampusonlineApi\Helpers\ApiException;
use Dbp\CampusonlineApi\PublicRestApi\Api;
use Dbp\CampusonlineApi\Rest\Tools;
use GuzzleHttp\Exception\GuzzleException;

class OrganizationsApi extends Api
{
    private const API_PATH = 'co/co-brm-core/org/api/organisations';

    private const UIDS_QUERY_PARAMETER_NAME = 'uids';

    public function getOrganizationByIdentifier(string $identifier): OrganizationResource
    {
        try {
            return new OrganizationResource(Tools::decodeJsonResponse(
                $this->connection->getClient()->get(
                    self::API_PATH.'?'.http_build_query([
                        self::UIDS_QUERY_PARAMETER_NAME => $identifier,
                    ])
                )));
        } catch (GuzzleException $guzzleException) {
            throw ApiException::fromGuzzleException($guzzleException);
        }
    }

    public function getOrganizations(int $firstItemIndex, int $maxNumItems, array $options = []): iterable
    {
        try {
            $response = $this->connection->getClient()->get(self::API_PATH.'?'.
                http_build_query(self::getPaginationQueryParameters($firstItemIndex, $maxNumItems)));
            foreach (Tools::decodeJsonResponse($response)['items'] ?? [] as $roomResourceData) {
                yield new OrganizationResource($roomResourceData);
            }
        } catch (GuzzleException $guzzleException) {
            throw ApiException::fromGuzzleException($guzzleException);
        }
    }
}
