<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Organization;

use Dbp\CampusonlineApi\LegacyWebService\Api;
use Dbp\CampusonlineApi\LegacyWebService\ApiException;
use Dbp\CampusonlineApi\LegacyWebService\Connection;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SimpleXMLElement;

class OrganizationUnitApi implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const ORG_UNIT_ID_PARAMETER_NAME = 'orgUnitID';

    private const URI = 'ws/webservice_v1.0/cdm/organization/xml';

    private $connection;
    private $rootOrgUnitId;

    public function __construct(Connection $connection, $rootOrgUnitId)
    {
        $this->connection = $connection;
        $this->rootOrgUnitId = $rootOrgUnitId;
    }

    /**
     * CAUTION: Campusonline seems to return '401 Unauthorized' instead of '404 Not found' in case the given ID is not found.
     *
     * @throws ApiException
     */
    public function getOrganizationUnitById(string $identifier, array $options = []): ?OrganizationUnitData
    {
        if (strlen($identifier) === 0) {
            throw new ApiException("identifier mustn't be empty");
        }

        $organizations = $this->getOrganizationUnitsInternal($identifier, $options);
        if (empty($organizations)) {
            throw new ApiException("response doesn't contain organization unit with ID ".$identifier, 404, true);
        }
        assert(count($organizations) === 1);

        return $organizations[0];
    }

    /**
     * @return OrganizationUnitData[]
     *
     * @throws ApiException
     */
    public function getOrganizationUnits(array $options = []): array
    {
        return $this->getOrganizationUnitsInternal('', $options);
    }

    /**
     * @param string $identifier the ID of the requested org unit or an empty string if all org units are requested
     *
     * @return OrganizationUnitData[]
     *
     * @throws ApiException
     */
    private function getOrganizationUnitsInternal(string $identifier, array $options): array
    {
        $parameters = [];
        $parameters[self::ORG_UNIT_ID_PARAMETER_NAME] = $identifier !== '' ? $identifier : $this->rootOrgUnitId;

        $responseBody = $this->connection->get(self::URI, $options[Api::LANGUAGE_PARAMETER_NAME] ?? '', $parameters);

        return $this->parseResponse($responseBody, $identifier);
    }

    /**
     * @return OrganizationUnitData[]
     *
     * @throws ApiException
     */
    private function parseResponse(string $responseBody, string $requestedId): array
    {
        $organizations = [];

        try {
            $xml = new SimpleXMLElement($responseBody);
        } catch (\Exception $e) {
            throw new ApiException('response body is not in valid XML format');
        }
        $nodes = $xml->xpath('.//orgUnit');

        foreach ($nodes as $node) {
            $identifier = trim((string) ($node->xpath('./orgUnitID')[0] ?? ''));
            if ($identifier === '') {
                continue;
            }

            $wasIdFound = false;
            if ($requestedId !== '') {
                if ($identifier === $requestedId) {
                    $wasIdFound = true;
                } else {
                    continue;
                }
            }

            $name = trim((string) ($node->xpath('./orgUnitName/text')[0] ?? ''));
            $code = trim((string) ($node->xpath('./orgUnitCode')[0] ?? ''));
            $type = trim((string) ($node->xpath('./orgUnitKind/subBlock')[0] ?? ''));
            $url = trim((string) ($node->xpath('./infoBlock/webLink/href')[0] ?? ''));

            $organization = new OrganizationUnitData();
            $organization->setIdentifier($identifier);
            $organization->setName($name);
            $organization->setCode($code);
            $organization->setType($type);
            $organization->setUrl($url);

            $organizations[] = $organization;

            if ($wasIdFound) {
                break;
            }
        }

        return $organizations;
    }
}
