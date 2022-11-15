<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Tests\LegacyWebService\Room;

use Dbp\CampusonlineApi\Helpers\FullPaginator;
use Dbp\CampusonlineApi\Helpers\PartialPaginator;
use Dbp\CampusonlineApi\LegacyWebService\Api;
use Dbp\CampusonlineApi\LegacyWebService\ApiException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class RoomApiTest extends TestCase
{
    /**
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

    /**
     * @throws ApiException
     */
    public function testGetRooms()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/rooms_response_1.xml')),
        ]);

        // full pagination of all items
        $paginator = $this->api->Room()->getRooms(['partialPagination' => false]);
        $this->assertTrue($paginator instanceof FullPaginator);
        $this->assertSame($paginator->getTotalNumItems(), 2);
        $this->assertCount(2, $paginator->getItems());

        $room = $paginator->getItems()[0];
        $this->assertSame('1234', $room->getIdentifier());
        $this->assertSame('Unit Test Projekt', $room->getAdditionalInfo());
        $this->assertSame('Teststraße 24, Erdgeschoß', $room->getAddress());
        $this->assertSame('IEEG123', $room->getName());
        $this->assertSame('29', $room->getPurposeID());
        $this->assertSame(42.42, $room->getFloorSize());
        $this->assertSame('https://online.tugraz.at/tug_online/ris.einzelraum?raumkey=1234', $room->getUrl());

        $room = $paginator->getItems()[1];
        $this->assertSame('1235', $room->getIdentifier());
        $this->assertSame('TECHN. TEST NORD', $room->getAdditionalInfo());
        $this->assertSame('Testgasse 4, 1.Obergeschoß', $room->getAddress());
        $this->assertSame('IE01234', $room->getName());
        $this->assertSame('14', $room->getPurposeID());
        $this->assertSame(51.59, $room->getFloorSize());
        $this->assertSame('https://online.tugraz.at/tug_online/ris.einzelraum?raumkey=1235', $room->getUrl());

        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/rooms_response_1.xml')),
        ]);

        // partial pagination of all items
        $paginator = $this->api->Room()->getRooms(['partialPagination' => true]);
        $this->assertTrue($paginator instanceof PartialPaginator);
        $this->assertCount(2, $paginator->getItems());

        $room = $paginator->getItems()[0];
        $this->assertSame('1234', $room->getIdentifier());
        $this->assertSame('Unit Test Projekt', $room->getAdditionalInfo());
        $this->assertSame('Teststraße 24, Erdgeschoß', $room->getAddress());
        $this->assertSame('IEEG123', $room->getName());
        $this->assertSame('29', $room->getPurposeID());
        $this->assertSame(42.42, $room->getFloorSize());
        $this->assertSame('https://online.tugraz.at/tug_online/ris.einzelraum?raumkey=1234', $room->getUrl());

        $room = $paginator->getItems()[1];
        $this->assertSame('1235', $room->getIdentifier());
        $this->assertSame('TECHN. TEST NORD', $room->getAdditionalInfo());
        $this->assertSame('Testgasse 4, 1.Obergeschoß', $room->getAddress());
        $this->assertSame('IE01234', $room->getName());
        $this->assertSame('14', $room->getPurposeID());
        $this->assertSame(51.59, $room->getFloorSize());
        $this->assertSame('https://online.tugraz.at/tug_online/ris.einzelraum?raumkey=1235', $room->getUrl());
    }

    /**
     * @throws ApiException
     */
    public function testGetRoomsFullPagination()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/rooms_response_1.xml')),
        ]);

        // full pagination of a page 1 with 1 item
        $paginator = $this->api->Room()->getRooms(['partialPagination' => false, 'perPage' => 1, 'page' => 1]);
        $this->assertTrue($paginator instanceof FullPaginator);
        $this->assertSame($paginator->getTotalNumItems(), 2);
        $this->assertSame($paginator->getCurrentPageNumber(), 1);
        $this->assertSame($paginator->getMaxNumItemsPerPage(), 1);
        $this->assertCount(1, $paginator->getItems());

        $room = $paginator->getItems()[0];
        $this->assertSame('1234', $room->getIdentifier());
        $this->assertSame('Unit Test Projekt', $room->getAdditionalInfo());
        $this->assertSame('Teststraße 24, Erdgeschoß', $room->getAddress());
        $this->assertSame('IEEG123', $room->getName());
        $this->assertSame('29', $room->getPurposeID());
        $this->assertSame(42.42, $room->getFloorSize());
        $this->assertSame('https://online.tugraz.at/tug_online/ris.einzelraum?raumkey=1234', $room->getUrl());

        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/rooms_response_1.xml')),
        ]);

        // explicit full pagination of a page 2 with 1 item
        $paginator = $this->api->Room()->getRooms(['partialPagination' => false, 'perPage' => 1, 'page' => 2]);
        $this->assertTrue($paginator instanceof FullPaginator);
        $this->assertSame($paginator->getTotalNumItems(), 2);
        $this->assertSame($paginator->getCurrentPageNumber(), 2);
        $this->assertSame($paginator->getMaxNumItemsPerPage(), 1);
        $this->assertCount(1, $paginator->getItems());

        $room = $paginator->getItems()[0];
        $this->assertSame('1235', $room->getIdentifier());
        $this->assertSame('TECHN. TEST NORD', $room->getAdditionalInfo());
        $this->assertSame('Testgasse 4, 1.Obergeschoß', $room->getAddress());
        $this->assertSame('IE01234', $room->getName());
        $this->assertSame('14', $room->getPurposeID());
        $this->assertSame(51.59, $room->getFloorSize());
        $this->assertSame('https://online.tugraz.at/tug_online/ris.einzelraum?raumkey=1235', $room->getUrl());
    }

    /**
     * @throws ApiException
     */
    public function testGetRoomsPartialPagination()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/rooms_response_1.xml')),
        ]);

        // partial pagination of a page 1 with 1 item
        $paginator = $this->api->Room()->getRooms(['partialPagination' => true, 'perPage' => 1, 'page' => 1]);
        $this->assertInstanceOf(PartialPaginator::class, $paginator);
        $this->assertSame($paginator->getCurrentPageNumber(), 1);
        $this->assertSame($paginator->getMaxNumItemsPerPage(), 1);
        $this->assertCount(1, $paginator->getItems());

        $room = $paginator->getItems()[0];
        $this->assertSame('1234', $room->getIdentifier());
        $this->assertSame('Unit Test Projekt', $room->getAdditionalInfo());
        $this->assertSame('Teststraße 24, Erdgeschoß', $room->getAddress());
        $this->assertSame('IEEG123', $room->getName());
        $this->assertSame('29', $room->getPurposeID());
        $this->assertSame(42.42, $room->getFloorSize());
        $this->assertSame('https://online.tugraz.at/tug_online/ris.einzelraum?raumkey=1234', $room->getUrl());

        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/rooms_response_1.xml')),
        ]);

        // partial pagination of a page 2 with 1 item
        $paginator = $this->api->Room()->getRooms(['partialPagination' => true, 'perPage' => 1, 'page' => 2]);
        $this->assertTrue($paginator instanceof PartialPaginator);
        $this->assertSame($paginator->getCurrentPageNumber(), 2);
        $this->assertSame($paginator->getMaxNumItemsPerPage(), 1);
        $this->assertCount(1, $paginator->getItems());

        $room = $paginator->getItems()[0];
        $this->assertSame('1235', $room->getIdentifier());
        $this->assertSame('TECHN. TEST NORD', $room->getAdditionalInfo());
        $this->assertSame('Testgasse 4, 1.Obergeschoß', $room->getAddress());
        $this->assertSame('IE01234', $room->getName());
        $this->assertSame('14', $room->getPurposeID());
        $this->assertSame(51.59, $room->getFloorSize());
        $this->assertSame('https://online.tugraz.at/tug_online/ris.einzelraum?raumkey=1235', $room->getUrl());
    }

    /**
     * Search filters: Pass, if ANY of the given search filters passes or if NONE is given.
     *
     * @throws ApiException
     */
    public function testGetRoomsSearchFilter()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/rooms_response_1.xml')),
        ]);

        // name search filter only with 1 match => 1 result
        $paginator = $this->api->Room()->getRooms(['partialPagination' => false, 'nameSearchFilter' => 'iee']);
        $this->assertTrue($paginator instanceof FullPaginator);
        $this->assertSame($paginator->getTotalNumItems(), 1);
        $this->assertCount(1, $paginator->getItems());

        $room = $paginator->getItems()[0];
        $this->assertSame('1234', $room->getIdentifier());
        $this->assertSame('Unit Test Projekt', $room->getAdditionalInfo());
        $this->assertSame('Teststraße 24, Erdgeschoß', $room->getAddress());
        $this->assertSame('IEEG123', $room->getName());
        $this->assertSame('29', $room->getPurposeID());
        $this->assertSame(42.42, $room->getFloorSize());
        $this->assertSame('https://online.tugraz.at/tug_online/ris.einzelraum?raumkey=1234', $room->getUrl());

        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/rooms_response_1.xml')),
        ]);

        // additional info search filter only with 1 match => 1 result
        $paginator = $this->api->Room()->getRooms(['partialPagination' => true, 'additionalInfoSearchFilter' => 'NORD']);
        $this->assertTrue($paginator instanceof PartialPaginator);
        $this->assertCount(1, $paginator->getItems());

        $room = $paginator->getItems()[0];
        $this->assertSame('1235', $room->getIdentifier());
        $this->assertSame('TECHN. TEST NORD', $room->getAdditionalInfo());
        $this->assertSame('Testgasse 4, 1.Obergeschoß', $room->getAddress());
        $this->assertSame('IE01234', $room->getName());
        $this->assertSame('14', $room->getPurposeID());
        $this->assertSame(51.59, $room->getFloorSize());
        $this->assertSame('https://online.tugraz.at/tug_online/ris.einzelraum?raumkey=1235', $room->getUrl());

        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/rooms_response_1.xml')),
        ]);

        // name filter only with no match => no results
        $paginator = $this->api->Room()->getRooms(['partialPagination' => false, 'nameSearchFilter' => 'not to be found']);
        $this->assertTrue($paginator instanceof FullPaginator);
        $this->assertSame($paginator->getTotalNumItems(), 0);
        $this->assertCount(0, $paginator->getItems());

        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/rooms_response_1.xml')),
        ]);

        // no search filters => all results
        $paginator = $this->api->Room()->getRooms(['partialPagination' => false, 'nameSearchFilter' => '', 'additionalInfoSearchFilter' => '']);
        $this->assertTrue($paginator instanceof FullPaginator);
        $this->assertSame($paginator->getTotalNumItems(), 2);
        $this->assertCount(2, $paginator->getItems());

        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/rooms_response_1.xml')),
        ]);

        // name search filter with no match, additional info search filter with 1 match => 1 result
        $paginator = $this->api->Room()->getRooms(['partialPagination' => false, 'nameSearchFilter' => 'not to be found', 'additionalInfoSearchFilter' => 'NORD']);
        $this->assertTrue($paginator instanceof FullPaginator);
        $this->assertSame($paginator->getTotalNumItems(), 1);
        $this->assertCount(1, $paginator->getItems());
    }

    /**
     * @throws ApiException
     */
    public function testGetRooms500()
    {
        $this->mockResponses([
            new Response(500, ['Content-Type' => 'text/xml;charset=utf-8'], ''),
        ]);

        $this->expectException(ApiException::class);
        $this->api->Room()->getRooms();
    }

    /**
     * @throws ApiException
     */
    public function testGetRoomsInvalidXML()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/rooms_response_invalid.xml')),
        ]);

        $this->expectException(ApiException::class);
        $this->api->Room()->getRoomById('1235');
    }

    /**
     * @throws ApiException
     */
    public function testGetRoomById()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/rooms_response_1.xml')),
        ]);

        $room = $this->api->Room()->getRoomById('1235');
        $this->assertSame('1235', $room->getIdentifier());
        $this->assertSame('TECHN. TEST NORD', $room->getAdditionalInfo());
        $this->assertSame('Testgasse 4, 1.Obergeschoß', $room->getAddress());
        $this->assertSame('IE01234', $room->getName());
        $this->assertSame('14', $room->getPurposeID());
        $this->assertSame(51.59, $room->getFloorSize());
        $this->assertSame('https://online.tugraz.at/tug_online/ris.einzelraum?raumkey=1235', $room->getUrl());
    }

    /**
     * @throws ApiException
     */
    public function testGetRoomByIdNotFound()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/rooms_response_1.xml')),
        ]);
        $this->expectException(ApiException::class);
        $this->expectExceptionCode(404);
        $this->api->Room()->getRoomById('123');
    }

    /**
     * @throws ApiException
     */
    public function testGetRoomById500()
    {
        $this->mockResponses([
            new Response(500, ['Content-Type' => 'text/xml;charset=utf-8'], ''),
        ]);

        $this->expectException(ApiException::class);
        $this->api->Room()->getRoomById('123');
    }
}
