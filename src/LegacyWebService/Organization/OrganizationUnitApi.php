<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Organization;

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

    public const ORG_UNIT_IDENTIFIER_XML_PATH = './orgUnitID';
    public const ORG_UNIT_RESOURCE_XML_NAME = 'orgUnit';
    public const ORG_UNIT_NAME_XML_PATH = './orgUnitName/text';

    private const URI = 'ws/webservice_v1.0/cdm/organization/xml';

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

    public static function getAttribute(\SimpleXMLElement $element, string $attributeName): ?\SimpleXMLElement
    {
        $xpathExpression = self::ATTRIBUTE_NAME_TO_XPATH_MAPPING[$attributeName] ?? null;
        if ($xpathExpression === null) {
            throw new ApiException(sprintf('attribute \'%s\'s not defined for %s node', $attributeName,
                self::ORG_UNIT_RESOURCE_XML_NAME));
        }

        return $element->xpath($xpathExpression)[0] ?? null;
    }

    public static function getAttributeString(\SimpleXMLElement $element, string $attributeName): ?string
    {
        $attributeElement = self::getAttribute($element, $attributeName);

        return $attributeElement === null ? null : trim((string) $attributeElement);
    }

    public static function getParentElement(\SimpleXMLElement $element): ?\SimpleXMLElement
    {
        return $element->xpath('..')[0] ?? null;
    }

    public function __construct(Connection $connection, string $rootOrgUnitId)
    {
        parent::__construct($connection, $rootOrgUnitId, self::ATTRIBUTE_NAME_TO_XPATH_MAPPING);
    }

    public function checkConnection(): void
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

        $uriParameters = [];
        $uriParameters[self::ORG_UNIT_ID_PARAMETER_NAME] = $this->rootOrgUnitId;
        $options[ResourceApi::GET_CHILD_IDS_OPTION_KEY] = true;

        $orgUnitData = $this->getItem($identifier, self::URI, $uriParameters, $options);

        if ($orgUnitData === null) {
            throw new ApiException('organization unit with ID not found: '.$identifier, 404, true);
        }

        if ($orgUnitData instanceof OrganizationUnitData === false) {
            throw new ApiException('internal error');
        }

        return $orgUnitData;
    }

    /**
     * @throws ApiException
     */
    public function getOrganizationUnits(array $options = []): Page
    {
        $uriParameters = [];
        $uriParameters[self::ORG_UNIT_ID_PARAMETER_NAME] = $this->rootOrgUnitId;
        $options[ResourceApi::GET_CHILD_IDS_OPTION_KEY] = true;

        return $this->getPage(self::URI, $uriParameters, $options);
    }

    protected function createResource(): ResourceData
    {
        return new OrganizationUnitData();
    }

    protected function isResourceNode(\SimpleXMLElement $node): array
    {
        [$isResourceNode, $checkChildren, $replacementParentId] = parent::isResourceNode($node);

        return [$isResourceNode && $node->getName() === self::ORG_UNIT_RESOURCE_XML_NAME, $checkChildren, $replacementParentId];
    }
}
