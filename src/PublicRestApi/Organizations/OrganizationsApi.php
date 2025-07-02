<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Organizations;

use Dbp\CampusonlineApi\Helpers\ApiException;
use Dbp\CampusonlineApi\PublicRestApi\AbstractApi;
use Dbp\CampusonlineApi\Rest\Tools;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class OrganizationsApi extends AbstractApi
{
    private const API_PATH = 'co/co-brm-core/org/api/';
    private const ORGANIZATIONS_API_PATH = self::API_PATH.'organisations';

    private const UIDS_QUERY_PARAMETER_NAME = 'uids';

    public function getOrganizationByIdentifier(string $identifier): OrganizationResource
    {
        try {
            $organizationResources = iterator_to_array($this->getOrganizationsFromResponse(
                $this->connection->getClient()->get(
                    self::ORGANIZATIONS_API_PATH.'?'.http_build_query([
                        self::UIDS_QUERY_PARAMETER_NAME => $identifier])
                )));
            if (empty($organizationResources)) {
                throw new ApiException('room not found', ApiException::HTTP_NOT_FOUND, true);
            }

            return $organizationResources[0];
        } catch (GuzzleException $guzzleException) {
            throw ApiException::fromGuzzleException($guzzleException);
        }
    }

    /**
     * @return iterable<OrganizationResource>
     */
    public function getOrganizations(int $firstItemIndex = 0, int $maxNumItems = 30, array $options = []): iterable
    {
        try {
            // WORKAROUND: CO ignores limit=0
            if ($maxNumItems === 0) {
                return [];
            } else {
                return $this->getOrganizationsFromResponse(
                    $this->connection->getClient()->get(self::ORGANIZATIONS_API_PATH.'?'.
                        http_build_query(self::getOffsetBasedPaginationQueryParameters($firstItemIndex, $maxNumItems))));
            }
        } catch (GuzzleException $guzzleException) {
            throw ApiException::fromGuzzleException($guzzleException);
        }
    }

    /**
     * @return iterable<OrganizationResource>
     */
    private function getOrganizationsFromResponse(ResponseInterface $response): iterable
    {
        foreach (Tools::decodeJsonResponse($response)['items'] ?? [] as $organizationResourceData) {
            yield new OrganizationResource($organizationResourceData);
        }
    }
}
