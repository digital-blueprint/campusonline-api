<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Organization;

use Dbp\CampusonlineApi\Helpers\Filters;
use Dbp\CampusonlineApi\Helpers\Page;
use Dbp\CampusonlineApi\LegacyWebService\ApiException;
use Dbp\CampusonlineApi\LegacyWebService\Connection;
use Dbp\CampusonlineApi\LegacyWebService\ResourceApi;
use Dbp\CampusonlineApi\LegacyWebService\ResourceData;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class OrganizationUnitApi extends ResourceApi implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const ORG_UNIT_ID_PARAMETER_NAME = 'orgUnitID';

    private const URI = 'ws/webservice_v1.0/cdm/organization/xml';

    private const ORG_UNIT_RESOURCE_XML_PATH = './/orgUnit';
    private const ORG_UNIT_IDENTIFIER_XML_PATH = './orgUnitID';
    private const ORG_UNIT_NAME_XML_PATH = './orgUnitName/text';

    private const ATTRIBUTE_NAME_TO_XPATH_MAPPING = [
        ResourceData::IDENTIFIER_ATTRIBUTE => self::ORG_UNIT_IDENTIFIER_XML_PATH,
        OrganizationUnitData::NAME_ATTRIBUTE => self::ORG_UNIT_NAME_XML_PATH,
        OrganizationUnitData::CODE_ATTRIBUTE => './orgUnitCode',
        OrganizationUnitData::KIND_NAME_ATTRIBUTE => './orgUnitKind/subBlock[@userDefined="name"]',
        OrganizationUnitData::KIND_CODE_ATTRIBUTE => './orgUnitKind/subBlock[@userDefined="codeDesignation"]',
        OrganizationUnitData::URL_ATTRIBUTE => './infoBlock/webLink/href',
        OrganizationUnitData::STREET_ATTRIBUTE => './contacts/contactData/adr/street',
        OrganizationUnitData::LOCALITY_ATTRIBUTE => './contacts/contactData/adr/locality',
        OrganizationUnitData::POSTAL_CODE_ATTRIBUTE => './contacts/contactData/adr/pcode',
        OrganizationUnitData::COUNTRY_ATTRIBUTE => './contacts/contactData/adr/country',
    ];

    public function __construct(Connection $connection, string $rootOrgUnitId)
    {
        parent::__construct($connection, $rootOrgUnitId, self::ORG_UNIT_RESOURCE_XML_PATH);
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
        if ($identifier === '') {
            throw new ApiException("identifier mustn't be empty");
        }

        ResourceApi::addEqualsIdFilter($options, $identifier);

        $paginator = $this->getOrganizationUnitsInternal($options, $identifier);

        $orgUnitItems = $paginator->getItems();
        if (empty($orgUnitItems)) {
            throw new ApiException("response doesn't contain organization unit with ID ".$identifier, 404, true);
        }
        assert(count($orgUnitItems) === 1);

        return $orgUnitItems[0];
    }

    /**
     * Returns a Paginator of organization units for the passed identifiers.
     * The order of the response is undefined and might not match the order of
     * the passed in identifiers.
     *
     * @param string[] $identifiers
     *
     * @throws ApiException
     */
    public function getOrganizationUnitsById(array $identifiers, array $options = []): Page
    {
        foreach ($identifiers as $identifier) {
            if ($identifier === '') {
                throw new ApiException("identifier mustn't be empty");
            }
        }

        ResourceApi::addFilter($options, ResourceData::IDENTIFIER_ATTRIBUTE, Filters::IN_OPERATOR, $identifiers);

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
    public function getOrganizationUnits(array $options = []): Page
    {
        return $this->getOrganizationUnitsInternal($options);
    }

    protected function createResource(\SimpleXMLElement $node): ResourceData
    {
        return new OrganizationUnitData();
    }

    protected function getAttributeNameToXpathExpressionMapping(): array
    {
        return self::ATTRIBUTE_NAME_TO_XPATH_MAPPING;
    }

    /**
     * @throws ApiException
     */
    private function getOrganizationUnitsInternal(array $options, string $identifier = null): Page
    {
        $parameters = [];
        $parameters[self::ORG_UNIT_ID_PARAMETER_NAME] = $identifier ?? $this->rootOrgUnitId;

        return $this->getResourcesInternal(self::URI, $parameters, $options);
    }
}
