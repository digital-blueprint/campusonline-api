<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Tests\DataServiceApi;

use Dbp\CampusonlineApi\DataServiceApi\Connection;
use Dbp\CampusonlineApi\DataServiceApi\PuQueryDataServiceApi;
use Dbp\CampusonlineApi\Helpers\ApiException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class PuQueryDataServiceTest extends TestCase
{
    private ?PuQueryDataServiceApi $api = null;

    private const RESPONSE_EMPTY = '
        {
            "type": "resources",
            "link": [],
            "resource": []
        }';

    private const RESPONSE_ONE = '
        {
            "type": "resources",
            "link": [],
            "resource": [
                {
                    "link": [],
                    "content": {
                        "type": "model-eg.dataservices.tugrazonline",
                        "DMSStudies": {
                            "STPERSONNR": 123456,
                            "STUDYKEY": "UF 066 921",
                            "STUDYTYPE": "Masterstudium",
                            "STUDYNAME": "Masterstudium; Computer Science",
                            "STUDYSEMESTER": 6,
                            "STUDYSTATUSKEY": "I"
                        }
                    }
                }
            ]
        }';

    private const RESPONSE_MULTIPLE = '
        {
            "type": "resources",
            "link": [],
            "resource": [
                {
                    "link": [],
                    "content": {
                        "type": "model-eg.dataservices.tugrazonline",
                        "DMSStudies": {
                            "STPERSONNR": 123456,
                            "STUDYKEY": "UF 066 921",
                            "STUDYTYPE": "Masterstudium",
                            "STUDYNAME": "Masterstudium; Computer Science",
                            "STUDYSEMESTER": 6,
                            "STUDYSTATUSKEY": "I"
                        }
                    }
                },
                {
                    "link": [],
                    "content": {
                        "type": "model-eg.dataservices.tugrazonline",
                        "DMSStudies": {
                            "STPERSONNR": 123456,
                            "STUDYKEY": "UF 066 922",
                            "STUDYTYPE": "Masterstudium",
                            "STUDYNAME": "Masterstudium; Data Science",
                            "STUDYSEMESTER": 4,
                            "STUDYSTATUSKEY": "A"
                        }
                    }
                }
            ]
        }';

    public function setUp(): void
    {
        parent::setUp();

        $connection = new Connection('https://dummy.at/dummy', 'foo', 'bar');
        $connection->setToken('nope');
        $this->api = new PuQueryDataServiceApi($connection, '');
        $this->mockResponses([]);
    }

    private function mockResponses(array $responses): void
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $this->api->getConnection()->setClientHandler($stack);
    }

    public function testGetResourceNone(): void
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], self::RESPONSE_EMPTY),
        ]);

        $this->assertNull($this->api->getResource('StPersonNr', '404'));
    }

    public function testGetResource(): void
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], self::RESPONSE_ONE),
        ]);

        $resource = $this->api->getResource('StPersonNr', '123456');
        $this->assertNotNull($resource);
        $this->assertSame('model-eg.dataservices.tugrazonline', $resource->type);
        $this->assertSame(123456, $resource->content['STPERSONNR']);
        $this->assertSame('UF 066 921', $resource->content['STUDYKEY']);
        $this->assertSame('Masterstudium', $resource->content['STUDYTYPE']);
        $this->assertSame('Masterstudium; Computer Science', $resource->content['STUDYNAME']);
        $this->assertSame(6, $resource->content['STUDYSEMESTER']);
        $this->assertSame('I', $resource->content['STUDYSTATUSKEY']);
    }

    public function testGetResourceMultipleError(): void
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], self::RESPONSE_MULTIPLE),
        ]);

        $this->expectException(ApiException::class);
        $this->api->getResource('StPersonNr', '123456');
    }

    public function testGetResourceCollectionNone(): void
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], self::RESPONSE_EMPTY),
        ]);

        $this->assertCount(0, $this->api->getResourceCollection(filters: ['StPersonNr' => '404']));
    }

    public function testGetStudiesOne(): void
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], self::RESPONSE_ONE),
        ]);

        $resources = $this->api->getResourceCollection(filters: ['StPersonNr' => '123456']);
        $this->assertCount(1, $resources);
        $resource = $resources[0];
        $this->assertSame('model-eg.dataservices.tugrazonline', $resource->type);
        $this->assertSame(123456, $resource->content['STPERSONNR']);
        $this->assertSame('UF 066 921', $resource->content['STUDYKEY']);
        $this->assertSame('Masterstudium', $resource->content['STUDYTYPE']);
        $this->assertSame('Masterstudium; Computer Science', $resource->content['STUDYNAME']);
        $this->assertSame(6, $resource->content['STUDYSEMESTER']);
        $this->assertSame('I', $resource->content['STUDYSTATUSKEY']);
    }

    public function testGetStudiesMultiple(): void
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], self::RESPONSE_MULTIPLE),
        ]);

        $resources = $this->api->getResourceCollection(filters: ['StPersonNr' => '123456']);
        $this->assertCount(2, $resources);
        $resource1 = $resources[0];
        $this->assertSame('model-eg.dataservices.tugrazonline', $resource1->type);
        $this->assertSame(123456, $resource1->content['STPERSONNR']);
        $this->assertSame('UF 066 921', $resource1->content['STUDYKEY']);
        $this->assertSame('Masterstudium', $resource1->content['STUDYTYPE']);
        $this->assertSame('Masterstudium; Computer Science', $resource1->content['STUDYNAME']);
        $this->assertSame(6, $resource1->content['STUDYSEMESTER']);
        $this->assertSame('I', $resource1->content['STUDYSTATUSKEY']);

        $resource2 = $resources[1];
        $this->assertSame('model-eg.dataservices.tugrazonline', $resource2->type);
        $this->assertSame(123456, $resource2->content['STPERSONNR']);
        $this->assertSame('UF 066 922', $resource2->content['STUDYKEY']);
        $this->assertSame('Masterstudium', $resource2->content['STUDYTYPE']);
        $this->assertSame('Masterstudium; Data Science', $resource2->content['STUDYNAME']);
        $this->assertSame(4, $resource2->content['STUDYSEMESTER']);
        $this->assertSame('A', $resource2->content['STUDYSTATUSKEY']);
    }
}
