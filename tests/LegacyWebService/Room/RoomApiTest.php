<?php

declare(strict_types=1);

namespace Dbp\Relay\RoomsConnectorCampusonlineBundle\Tests\Service;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Dbp\Relay\CoreBundle\Exception\ApiError;
use Dbp\Relay\RoomsConnectorCampusonlineBundle\Service\RoomProvider;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Monolog\Handler\NullHandler;
use Monolog\Logger;

class RoomProviderTest extends ApiTestCase
{
    /**
     * @var RoomProvider
     */
    private $api;

    protected function setUp(): void
    {
        parent::setUp();

        $nullLogger = new Logger('dummy', [new NullHandler()]);
        $this->api = new RoomProvider($nullLogger);
        $this->mockResponses([]);
    }

    private function mockResponses(array $responses)
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $this->api->setClientHandler($stack);
    }

    public function testGetRooms()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/rooms_response_1.xml')),
        ]);

        $rooms = $this->api->getRooms();
        $this->assertCount(2, $rooms);
        $room = $rooms[0];
        $this->assertSame('1234', $room->getIdentifier());
        $this->assertSame('Teststraße 24, Erdgeschoß', $room->getAddress());
        $this->assertSame('IEEG123', $room->getAlternateName());
        $this->assertSame('29', $room->getPermittedUsage());
        $this->assertSame('Labor', $room->getDescription());
        $this->assertSame('42.42', $room->getFloorSize());
        $this->assertSame('https://online.tugraz.at/tug_online/ris.einzelraum?raumkey=1234', $room->getUrl());
        $this->assertSame('Unit Test Projekt', $room->getName());
    }

    public function testGetRooms500()
    {
        $this->mockResponses([
            new Response(500, ['Content-Type' => 'text/xml;charset=utf-8'], ''),
        ]);

        $this->expectException(ApiError::class);
        $this->api->getRooms();
    }

    public function testGetRoomById()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/rooms_response_1.xml')),
        ]);

        $room = $this->api->getRoomById('1235');
        $this->assertSame('1235', $room->getIdentifier());
        $this->assertSame('Testgasse 4, 1.Obergeschoß', $room->getAddress());
        $this->assertSame('Labor - EDV', $room->getDescription());
        $this->assertSame('https://online.tugraz.at/tug_online/ris.einzelraum?raumkey=1235', $room->getUrl());
    }

    public function testGetRoomByIdNotFound()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/rooms_response_1.xml')),
        ]);
        $room = $this->api->getRoomById('123');
        $this->assertNull($room);
    }

    public function testGetRoomById500()
    {
        $this->mockResponses([
            new Response(500, ['Content-Type' => 'text/xml;charset=utf-8'], ''),
        ]);

        $this->expectException(ApiError::class);
        $this->api->getRoomById('123');
    }
}
