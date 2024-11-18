<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Tests\LegacyWebService\Organization;

use Dbp\CampusonlineApi\Helpers\Filters;
use Dbp\CampusonlineApi\Helpers\Page;
use Dbp\CampusonlineApi\LegacyWebService\Api;
use Dbp\CampusonlineApi\LegacyWebService\ApiException;
use Dbp\CampusonlineApi\LegacyWebService\Organization\OrganizationUnitApi;
use Dbp\CampusonlineApi\LegacyWebService\Organization\OrganizationUnitData;
use Dbp\CampusonlineApi\LegacyWebService\ResourceApi;
use Dbp\CampusonlineApi\LegacyWebService\ResourceData;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class OrganizationUnitApiTest extends TestCase
{
    /** @var Api */
    private $api;

    protected function setUp(): void
    {
        parent::setUp();

        $this->api = new Api('http://localhost', 'token', '0', null,
            new ArrayAdapter(3600, true, 3600, 356), 3600);
        $this->mockResponses([]);
    }

    private function mockResponses(array $responses)
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $this->api->setClientHandler($stack);
    }

    private function getOrgUnitApi(): OrganizationUnitApi
    {
        return $this->api->OrganizationUnit();
    }

    /**
     * @throws ApiException
     */
    public function testGetOrganizationById()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/co_orgunit_response.xml')),
        ]);

        $org = $this->getOrgUnitApi()->getOrganizationUnitById('2322', ['en']);
        $this->assertSame('2322', $org->getIdentifier());
        $this->assertSame('Institute of Fundamentals and Theory in Electrical  Engineering', $org->getName());
        $this->assertSame('4370', $org->getCode());
        $this->assertSame('https://online.tugraz.at/tug_online/wborg.display?pOrgNr=2322', $org->getUrl());
        $this->assertSame('Inffeldgasse 18/I', $org->getStreet());
        $this->assertSame('Graz', $org->getLocality());
        $this->assertSame('8010', $org->getPostalCode());
        $this->assertSame('AT', $org->getCountry());
        $this->assertSame('INSTITUT', $org->getKindCode());
        $this->assertSame('Institute', $org->getKindName());

        $this->assertSame('2322', $org->getData()[ResourceData::IDENTIFIER_ATTRIBUTE]);
        $this->assertSame('Institute of Fundamentals and Theory in Electrical  Engineering', $org->getData()[OrganizationUnitData::NAME_ATTRIBUTE]);
        $this->assertSame('4370', $org->getData()[OrganizationUnitData::CODE_ATTRIBUTE]);
        $this->assertSame('https://online.tugraz.at/tug_online/wborg.display?pOrgNr=2322', $org->getData()[OrganizationUnitData::URL_ATTRIBUTE]);
        $this->assertSame('Inffeldgasse 18/I', $org->getData()[OrganizationUnitData::STREET_ATTRIBUTE]);
        $this->assertSame('Graz', $org->getData()[OrganizationUnitData::LOCALITY_ATTRIBUTE]);
        $this->assertSame('8010', $org->getData()[OrganizationUnitData::POSTAL_CODE_ATTRIBUTE]);
        $this->assertSame('AT', $org->getData()[OrganizationUnitData::COUNTRY_ATTRIBUTE]);
        $this->assertSame('INSTITUT', $org->getData()[OrganizationUnitData::KIND_CODE_ATTRIBUTE]);
        $this->assertSame('Institute', $org->getData()[OrganizationUnitData::KIND_NAME_ATTRIBUTE]);
    }

    public function testCheckConnection()
    {
        $this->mockResponses([
            new Response(400, ['Content-Type' => 'text/xml;charset=utf-8'], ''),
            new Response(404, ['Content-Type' => 'text/xml;charset=utf-8'], ''),
        ]);

        $this->getOrgUnitApi()->checkConnection();
        $this->expectNotToPerformAssertions();
    }

    /**
     * @throws ApiException
     */
    public function testGetOrganizationByIdNoPermission()
    {
        $this->mockResponses([
            new Response(403, [], 'error'),
        ]);
        $this->expectException(ApiException::class);
        $this->getOrgUnitApi()->getOrganizationUnitById('2234-F1234', ['en']);
    }

    /**
     * @throws ApiException
     */
    public function testGetAllOrganizations()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/co_orgunit_response_nested.xml')),
        ]);
        $page = $this->getOrgUnitApi()->getOrganizationUnits(['en']);
        $this->assertInstanceOf(Page::class, $page);
        $this->assertSame(3, $page->getMaxNumItemsPerPage());
        $this->assertSame(1, $page->getCurrentPageNumber());

        $orgUnits = $page->getItems();
        $this->assertCount(3, $orgUnits);

        $this->assertSame('2391', $orgUnits[0]->getIdentifier());
        $this->assertSame('6350', $orgUnits[0]->getCode());
        $this->assertSame('18454', $orgUnits[1]->getIdentifier());
        $this->assertSame('6352', $orgUnits[1]->getCode());
        $this->assertSame('18452', $orgUnits[2]->getIdentifier());
        $this->assertSame('6351', $orgUnits[2]->getCode());
    }

    /**
     * @throws ApiException
     */
    public function testGetOrganizationFilters()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/co_orgunit_response_nested.xml')),
        ]);

        // case-insensitive name filter -> match
        $options = [];
        ResourceApi::addFilter($options, OrganizationUnitData::NAME_ATTRIBUTE, Filters::CONTAINS_CI_OPERATOR, 'working', Filters::LOGICAL_OR_OPERATOR);
        $page = $this->getOrgUnitApi()->getOrganizationUnits($options);
        $orgUnits = $page->getItems();
        $this->assertCount(2, $orgUnits);

        $this->assertSame('Working Group Spectroscopy and Electrochemistry', $orgUnits[0]->getName());
        $this->assertSame('Working Group Structure Science', $orgUnits[1]->getName());

        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/co_orgunit_response_nested.xml')),
        ]);

        // case-sensitive name filter -> no match
        $options = [];
        ResourceApi::addFilter($options, OrganizationUnitData::NAME_ATTRIBUTE, Filters::CONTAINS_OPERATOR, 'working', Filters::LOGICAL_OR_OPERATOR);
        $page = $this->getOrgUnitApi()->getOrganizationUnits($options);
        $orgUnits = $page->getItems();
        $this->assertCount(0, $orgUnits);
    }

    /**
     * @throws ApiException
     */
    public function testGetAllOrganizationsPagination()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/co_orgunit_response_nested.xml')),
        ]);
        $page = $this->getOrgUnitApi()->getOrganizationUnits(['perPage' => 1, 'page' => 3]);
        $this->assertInstanceOf(Page::class, $page);
        $this->assertSame(1, $page->getMaxNumItemsPerPage());
        $this->assertSame(3, $page->getCurrentPageNumber());

        $orgUnits = $page->getItems();
        $this->assertCount(1, $orgUnits);

        $orgUnit = $orgUnits[0];
        $this->assertSame('18452', $orgUnit->getIdentifier());
        $this->assertSame('6351', $orgUnit->getCode());
    }
}
