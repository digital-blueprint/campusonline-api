<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Tests\Rest;

use Dbp\CampusonlineApi\Rest\Api;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class GenericTest extends TestCase
{
    /**
     * @var Api
     */
    private $api;

    public function setUp(): void
    {
        parent::setUp();

        $this->api = new Api('http://localhost', 'nope', 'nope');
        $this->api->getConnection()->setToken('foo');
        $this->mockResponses([]);
    }

    private function mockResponses(array $responses)
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $this->api->getConnection()->setClientHandler($stack);
    }

    public function testGetSingle()
    {
        $RESPONSE = '
{
    "type": "resources",
    "link": [],
    "resource": [
        {
            "link": [],
            "content": {
                "type": "model-CO_LOC_DS.API_PROJEKTE",
                "API_PROJEKTE": {
                    "ID": "F123",
                    "TITEL": "Lorem ipsum dolor sit amet, consectetur adipiscing elit",
                    "BESCHREIBUNG": "sed do eiusmod tempor incididunt ut labore et dolore magna aliqua",
                    "BEGINN": {
                        "value": "1997-01-01"
                    },
                    "ENDE": {
                        "value": "1999-01-01"
                    },
                    "SPRACHE": "EN"
                }
            }
        }
    ]
}';

        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], $RESPONSE),
        ]);

        $generic = $this->api->Generic('loc_apiProjekte');
        $item = $generic->getResource('ID', 'F123');
        $this->assertNotNull($item);
        $this->assertSame('model-CO_LOC_DS.API_PROJEKTE', $item->type);
        $this->assertSame('F123', $item->content['ID']);
        $this->assertSame('EN', $item->content['SPRACHE']);
    }

    public function testGetSingleNotFound()
    {
        $RESPONSE = '
{
    "type": "resources",
    "link": [],
    "resource": [
    ]
}';

        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], $RESPONSE),
        ]);

        $generic = $this->api->Generic('loc_apiProjekte');
        $item = $generic->getResource('ID', 'F123');
        $this->assertNull($item);
    }

    public function testGetTwo()
    {
        $RESPONSE = '
{
    "type": "resources",
    "link": [],
    "resource": [
        {
            "link": [],
            "content": {
                "type": "model-CO_LOC_DS.API_EXTERNE_ORGANISATIONEN",
                "API_EXTERNE_ORGANISATIONEN": {
                    "ID": 5,
                    "NAME": "Eine Firma mit einem Namen",
                    "NAME_MASTERORG_L1": null,
                    "NAME_MASTERORG_L2": null,
                    "NAME_ENGL": null,
                    "NAME_MASTERORG_L1_ENGL": null,
                    "NAME_MASTERORG_L2_ENGL": null,
                    "LAND": "Österreich",
                    "PLZ_ORT": "1030 Wien",
                    "STRASSE": "Brockmanngasse 4242",
                    "EMAIL_ADRESSE": "office@example.com",
                    "WWW_HOMEPAGE": "http://www.example.com",
                    "EXT_ORGTYP_ID": 41,
                    "EXT_ORGTYP_NAME": "Großunternehmen",
                    "FIRMENBUCHNUMMER": "FN 12345 w",
                    "NAME_ALIAS": null
                }
            }
        },
        {
            "link": [],
            "content": {
                "type": "model-CO_LOC_DS.API_EXTERNE_ORGANISATIONEN",
                "API_EXTERNE_ORGANISATIONEN": {
                    "ID": 6,
                    "NAME": "Erika Musterfrau",
                    "NAME_MASTERORG_L1": null,
                    "NAME_MASTERORG_L2": null,
                    "NAME_ENGL": null,
                    "NAME_MASTERORG_L1_ENGL": null,
                    "NAME_MASTERORG_L2_ENGL": null,
                    "LAND": "Österreich",
                    "PLZ_ORT": "8010 Graz",
                    "STRASSE": "Hauptstr. 42",
                    "EMAIL_ADRESSE": null,
                    "WWW_HOMEPAGE": null,
                    "EXT_ORGTYP_ID": 10,
                    "EXT_ORGTYP_NAME": "andere Organisationen",
                    "FIRMENBUCHNUMMER": null,
                    "NAME_ALIAS": null
                }
            }
        }
    ]
}';

        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], $RESPONSE),
        ]);

        $generic = $this->api->Generic('loc_apiExterneOrganisationen');
        $items = $generic->getResourceCollection([], 0, 2);

        $this->assertCount(2, $items);
        $item = $items[0];
        $this->assertSame('model-CO_LOC_DS.API_EXTERNE_ORGANISATIONEN', $item->type);
        $this->assertSame(5, $item->content['ID']);
        $this->assertSame('FN 12345 w', $item->content['FIRMENBUCHNUMMER']);

        $item = $items[1];
        $this->assertSame('model-CO_LOC_DS.API_EXTERNE_ORGANISATIONEN', $item->type);
        $this->assertSame(6, $item->content['ID']);
        $this->assertSame(null, $item->content['FIRMENBUCHNUMMER']);
    }

    public function testGetPage()
    {
        $EMPTY_RESPONSE = '
{
    "type": "resources",
    "link": [],
    "resource": []
}
';
        $RESPONSE = '
{
    "type": "resources",
    "link": [],
    "resource": [
        {
            "link": [],
            "content": {
                "type": "model-CO_LOC_DS.API_EXTERNE_ORGANISATIONEN",
                "API_EXTERNE_ORGANISATIONEN": {
                    "ID": 5,
                    "NAME": "Eine Firma mit einem Namen",
                    "NAME_MASTERORG_L1": null,
                    "NAME_MASTERORG_L2": null,
                    "NAME_ENGL": null,
                    "NAME_MASTERORG_L1_ENGL": null,
                    "NAME_MASTERORG_L2_ENGL": null,
                    "LAND": "Österreich",
                    "PLZ_ORT": "1030 Wien",
                    "STRASSE": "Brockmanngasse 4242",
                    "EMAIL_ADRESSE": "office@example.com",
                    "WWW_HOMEPAGE": "http://www.example.com",
                    "EXT_ORGTYP_ID": 41,
                    "EXT_ORGTYP_NAME": "Großunternehmen",
                    "FIRMENBUCHNUMMER": "FN 12345 w",
                    "NAME_ALIAS": null
                }
            }
        },
        {
            "link": [],
            "content": {
                "type": "model-CO_LOC_DS.API_EXTERNE_ORGANISATIONEN",
                "API_EXTERNE_ORGANISATIONEN": {
                    "ID": 6,
                    "NAME": "Erika Musterfrau",
                    "NAME_MASTERORG_L1": null,
                    "NAME_MASTERORG_L2": null,
                    "NAME_ENGL": null,
                    "NAME_MASTERORG_L1_ENGL": null,
                    "NAME_MASTERORG_L2_ENGL": null,
                    "LAND": "Österreich",
                    "PLZ_ORT": "8010 Graz",
                    "STRASSE": "Hauptstr. 42",
                    "EMAIL_ADRESSE": null,
                    "WWW_HOMEPAGE": null,
                    "EXT_ORGTYP_ID": 10,
                    "EXT_ORGTYP_NAME": "andere Organisationen",
                    "FIRMENBUCHNUMMER": null,
                    "NAME_ALIAS": null
                }
            }
        }
    ]
}';

        $generic = $this->api->Generic('loc_apiExterneOrganisationen');

        // fake that CO returns only 2 items per request, so we have to repeat calls
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], $RESPONSE),
            new Response(200, ['Content-Type' => 'application/json'], $RESPONSE),
            new Response(200, ['Content-Type' => 'application/json'], $RESPONSE),
        ]);
        $items = $generic->getResourcePage([], 1, 6);
        $this->assertCount(6, $items);

        // Fake that there are only 2 items in CO
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], $RESPONSE),
            new Response(200, ['Content-Type' => 'application/json'], $EMPTY_RESPONSE),
        ]);
        $items = $generic->getResourcePage([], 1, 5);
        $this->assertCount(2, $items);

        // Fake that CO returns more than we wanted
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], $RESPONSE),
            new Response(200, ['Content-Type' => 'application/json'], $RESPONSE),
        ]);
        $items = $generic->getResourcePage([], 1, 3);
        $this->assertCount(3, $items);
    }
}
