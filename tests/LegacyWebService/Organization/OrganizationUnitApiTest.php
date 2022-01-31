<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Tests\LegacyWebService\Organization;

use Dbp\CampusonlineApi\LegacyWebService\Api;
use Dbp\CampusonlineApi\LegacyWebService\Organization\OrganizationUnitApi;
use Dbp\CampusonlineApi\Rest\ApiException;
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

    public function testGetOrganizationById()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/co_orgunit_response.xml')),
        ]);

        $org = $this->getOrgUnitApi()->getOrganizationUnitById('2322', ['en']);
        $this->assertNotNull($org);
        if ($org !== null) {
            $this->assertSame('2322', $org->getIdentifier());
            $this->assertSame('Institute of Fundamentals and Theory in Electrical  Engineering', $org->getName());
            $this->assertSame('4370', $org->getCode());
            $this->assertSame('https://online.tugraz.at/tug_online/wborg.display?pOrgNr=2322', $org->getUrl());
        }
    }

    public function testGetOrganizationByIdNoPermission()
    {
        $this->mockResponses([
            new Response(403, [], 'error'),
        ]);
        $this->expectException(ApiException::class);
        $this->getOrgUnitApi()->getOrganizationUnitById('2234-F1234', ['en']);
    }

    public function testGetAllOrganizations()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/co_orgunit_response_nested.xml')),
        ]);
        $orgUnits = $this->getOrgUnitApi()->getOrganizationUnits(['en']);
        $this->assertCount(3, $orgUnits);
        $this->assertSame('2391', $orgUnits[0]->getIdentifier());
        $this->assertSame('6350', $orgUnits[0]->getCode());
        $this->assertSame('18454', $orgUnits[1]->getIdentifier());
        $this->assertSame('6352', $orgUnits[1]->getCode());
        $this->assertSame('18452', $orgUnits[2]->getIdentifier());
        $this->assertSame('6351', $orgUnits[2]->getCode());
    }
}
