<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Organization;

use Dbp\CampusonlineApi\Helpers\Filters;
use Dbp\CampusonlineApi\Helpers\Paginator;
use Dbp\CampusonlineApi\LegacyWebService\Address\AddressData;
use Dbp\CampusonlineApi\LegacyWebService\ApiException;
use Dbp\CampusonlineApi\LegacyWebService\Connection;
use Dbp\CampusonlineApi\LegacyWebService\ResourceApi;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SimpleXMLElement;

class OrganizationUnitApi extends ResourceApi implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const ORG_UNIT_ID_PARAMETER_NAME = 'orgUnitID';

    private const URI = 'ws/webservice_v1.0/cdm/organization/xml';

    private const ORG_UNIT_RESOURCE_XML_PATH = './/orgUnit';
    private const ORG_UNIT_IDENTIFIER_XML_PATH = './orgUnitID';
    private const ORG_UNIT_NAME_XML_PATH = './orgUnitName/text';

    public function __construct(Connection $connection, string $rootOrgUnitId)
    {
        parent::__construct($connection, $rootOrgUnitId,
            self::ORG_UNIT_RESOURCE_XML_PATH, self::ORG_UNIT_IDENTIFIER_XML_PATH);
    }

    public function checkConnection()
    {
        // To check if the API can respond with a proper error
        $this->expectGetError(self::URI, [], 400);
        // To check that the token is valid (otherwise we get 401)
        $this->expectGetError(self::URI, [self::ORG_UNIT_ID_PARAMETER_NAME => ''], 404);
    }

    /**
     * CAUTION: Campusonline seems to return '401 Unauthorized' instead of '404 Not found' in case the given ID is not found.
     *
     * @throws ApiException
     */
    public function getOrganizationUnitById(string $identifier, array $options = []): OrganizationUnitData
    {
        if (strlen($identifier) === 0) {
            throw new ApiException("identifier mustn't be empty");
        }

        $options[Filters::IDENTIFIERS_FILTER] = [$identifier];

        $paginator = $this->getOrganizationUnitsInternal($options);

        $orgUnitItems = $paginator->getItems();
        if (empty($orgUnitItems)) {
            throw new ApiException("response doesn't contain organization unit with ID ".$identifier, 404, true);
        }
        assert(count($orgUnitItems) === 1);

        return $orgUnitItems[0];
    }

    /**
     * Returns a Paginator of OrganizationUnitData for the passed identifiers.
     * The order of the response is undefined and might not match the order of
     * the passed in identifiers.
     *
     * @param string[] $identifiers
     *
     * @throws ApiException
     */
    public function getOrganizationUnitsById(array $identifiers, array $options = []): Paginator
    {
        foreach ($identifiers as $identifier) {
            if (strlen($identifier) === 0) {
                throw new ApiException("identifier mustn't be empty");
            }
        }

        $options[Filters::IDENTIFIERS_FILTER] = $identifiers;

        $paginator = $this->getOrganizationUnitsInternal($options);
        $orgUnitItems = $paginator->getItems();
        if (count($orgUnitItems) !== count($identifiers)) {
            throw new ApiException("response doesn't contain all requested organization units", 404, true);
        }

        return $paginator;
    }

    /**
     * @throws ApiException
     */
    public function getOrganizationUnits(array $options = []): Paginator
    {
        return $this->getOrganizationUnitsInternal($options);
    }

    /**
     * @throws ApiException
     */
    private function getOrganizationUnitsInternal(array $options): Paginator
    {
        $parameters = [];
        $requestedIdentifiers = $options[Filters::IDENTIFIERS_FILTER] ?? [];
        $parameters[self::ORG_UNIT_ID_PARAMETER_NAME] =
            count($requestedIdentifiers) === 1 ? $requestedIdentifiers[0] : $this->rootOrgUnitId;

        return $this->getResourcesInternal(self::URI, $parameters, $options);
    }

    protected function createResource(SimpleXMLElement $node, string $identifier): object
    {
        $orgUnit = new OrganizationUnitData();
        $orgUnit->setIdentifier($identifier);

        $name = $this->getResourceName($node);
        $orgUnit->setName($name);

        $codeNode = $node->xpath('./orgUnitCode');
        if ($codeNode) {
            $orgUnit->setCode((string) $codeNode[0]);
        }

        $kindNameNode = $node->xpath('./orgUnitKind/subBlock[@userDefined="name"]');
        if ($kindNameNode) {
            $orgUnit->setKindName((string) $kindNameNode[0]);
        }

        $kindCodeNode = $node->xpath('./orgUnitKind/subBlock[@userDefined="codeDesignation"]');
        if ($kindCodeNode) {
            $orgUnit->setKindCode((string) $kindCodeNode[0]);
        }

        $urlNode = $node->xpath('./infoBlock/webLink/href');
        if ($codeNode) {
            $orgUnit->setUrl((string) $urlNode[0]);
        }

        $addressNode = $node->xpath('./contacts/contactData/adr')[0] ?? null;
        $addressData = $addressNode !== null ? AddressData::fromSimpleXmlElement($addressNode) : null;
        if ($addressData !== null) {
            $orgUnit->setAddress($addressData);
        }

        return $orgUnit;
    }

    protected function getResourceName(SimpleXMLElement $node): string
    {
        return self::getResourcePropertyOrEmptyString($node, self::ORG_UNIT_NAME_XML_PATH);
    }
}
