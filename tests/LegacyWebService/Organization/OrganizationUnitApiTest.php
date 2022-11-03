<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Tests\LegacyWebService\Organization;

use Dbp\CampusonlineApi\Helpers\FullPaginator;
use Dbp\CampusonlineApi\Helpers\PartialPaginator;
use Dbp\CampusonlineApi\LegacyWebService\Api;
use Dbp\CampusonlineApi\LegacyWebService\ApiException;
use Dbp\CampusonlineApi\LegacyWebService\Organization\OrganizationUnitApi;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class OrganizationUnitApiTest extends TestCase
{
    /*
     * @var Api
     */
    private $api;

    protected function setUp(): void
    {
        parent::setUp();

        $this->api = new Api('http://localhost', 'token', '0');
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
        $paginator = $this->getOrgUnitApi()->getOrganizationUnits(['en']);
        $this->assertInstanceOf(FullPaginator::class, $paginator);
        $this->assertSame(3, $paginator->getTotalNumItems());
        $this->assertSame(3, $paginator->getMaxNumItemsPerPage());
        $this->assertSame(1, $paginator->getCurrentPageNumber());

        $orgUnits = $paginator->getItems();
        $this->assertCount(3, $orgUnits);

        $this->assertCount(3, $orgUnits);
        $this->assertSame('2391', $orgUnits[0]->getIdentifier());
        $this->assertSame('6350', $orgUnits[0]->getCode());
        $this->assertSame('18454', $orgUnits[1]->getIdentifier());
        $this->assertSame('6352', $orgUnits[1]->getCode());
        $this->assertSame('18452', $orgUnits[2]->getIdentifier());
        $this->assertSame('6351', $orgUnits[2]->getCode());
    }

    public function testGetSomeOrganizations()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/co_orgunit_response_nested.xml')),
        ]);
        $api = $this->getOrgUnitApi();
        $result = $api->getOrganizationUnitsById(['18452', '18454']);
        $items = $result->getItems();
        $this->assertCount(2, $items);
        $this->assertSame($items[0]->getIdentifier(), '18454');
        $this->assertSame($items[1]->getIdentifier(), '18452');
    }

    /**
     * @throws ApiException
     */
    public function testGetAllOrganizationsPagination()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/co_orgunit_response_nested.xml')),
        ]);
        $paginator = $this->getOrgUnitApi()->getOrganizationUnits(['perPage' => 1, 'page' => 3]);
        $this->assertInstanceOf(FullPaginator::class, $paginator);
        $this->assertSame(3, $paginator->getTotalNumItems());
        $this->assertSame(1, $paginator->getMaxNumItemsPerPage());
        $this->assertSame(3, $paginator->getCurrentPageNumber());

        $orgUnits = $paginator->getItems();
        $this->assertCount(1, $orgUnits);

        $orgUnit = $orgUnits[0];
        $this->assertSame('18452', $orgUnit->getIdentifier());
        $this->assertSame('6351', $orgUnit->getCode());
    }

    /**
     * @throws ApiException
     */
    public function testGetAllOrganizationsPartialPagination()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/co_orgunit_response_nested.xml')),
        ]);
        $paginator = $this->getOrgUnitApi()->getOrganizationUnits(['partialPagination' => true, 'perPage' => 1, 'page' => 2]);
        $this->assertInstanceOf(PartialPaginator::class, $paginator);
        $this->assertSame(1, $paginator->getMaxNumItemsPerPage());
        $this->assertSame(2, $paginator->getCurrentPageNumber());

        $orgUnits = $paginator->getItems();
        $this->assertCount(1, $orgUnits);

        $orgUnit = $orgUnits[0];
        $this->assertSame('18454', $orgUnit->getIdentifier());
        $this->assertSame('6352', $orgUnit->getCode());
    }
}
