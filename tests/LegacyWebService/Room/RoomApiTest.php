<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Tests\LegacyWebService\Room;

use Dbp\CampusonlineApi\Helpers\Filters;
use Dbp\CampusonlineApi\Helpers\Page;
use Dbp\CampusonlineApi\LegacyWebService\Api;
use Dbp\CampusonlineApi\LegacyWebService\ApiException;
use Dbp\CampusonlineApi\LegacyWebService\ResourceApi;
use Dbp\CampusonlineApi\LegacyWebService\ResourceData;
use Dbp\CampusonlineApi\LegacyWebService\Room\RoomData;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class RoomApiTest extends TestCase
{
    /**
     * @var Api
     */
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

    public function testCheckConnection()
    {
        // NOTE: room API returns 404 if no id is specified, where other APIs (course, organization, ...) return 400
        $this->mockResponses([
            new Response(404, ['Content-Type' => 'text/xml;charset=utf-8'], ''),
            new Response(404, ['Content-Type' => 'text/xml;charset=utf-8'], ''),
        ]);

        $this->api->Room()->checkConnection();
        $this->assertTrue(true);
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
        $page = $this->api->Room()->getRooms();
        $this->assertInstanceOf(Page::class, $page);
        $this->assertCount(2, $page->getItems());

        $room = $page->getItems()[0];
        $this->assertSame('1234', $room->getIdentifier());
        $this->assertSame('Unit Test Projekt', $room->getAdditionalInfo());
        $this->assertSame('Teststraße 24, Erdgeschoß', $room->getAddress());
        $this->assertSame('IEEG123', $room->getCode());
        $this->assertSame('29', $room->getPurposeID());
        $this->assertSame(42.42, $room->getFloorSize());
        $this->assertSame('https://online.tugraz.at/tug_online/ris.einzelraum?raumkey=1234', $room->getUrl());
        $this->assertSame(['4321'], $room->getOrganizations());

        $room = $page->getItems()[1];
        $this->assertSame('1235', $room->getIdentifier());
        $this->assertSame('TECHN. TEST NORD', $room->getAdditionalInfo());
        $this->assertSame('Testgasse 4, 1.Obergeschoß', $room->getAddress());
        $this->assertSame('IE01234', $room->getCode());
        $this->assertSame('14', $room->getPurposeID());
        $this->assertSame(51.59, $room->getFloorSize());
        $this->assertSame('https://online.tugraz.at/tug_online/ris.einzelraum?raumkey=1235', $room->getUrl());
        $this->assertSame(['2345', '2346'], $room->getOrganizations());

        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/rooms_response_1.xml')),
        ]);

        // partial pagination of all items
        $page = $this->api->Room()->getRooms(['partialPagination' => true]);
        $this->assertInstanceOf(Page::class, $page);
        $this->assertCount(2, $page->getItems());

        $room = $page->getItems()[0];
        $this->assertSame('1234', $room->getIdentifier());
        $this->assertSame('Unit Test Projekt', $room->getAdditionalInfo());
        $this->assertSame('Teststraße 24, Erdgeschoß', $room->getAddress());
        $this->assertSame('IEEG123', $room->getCode());
        $this->assertSame('29', $room->getPurposeID());
        $this->assertSame(42.42, $room->getFloorSize());
        $this->assertSame('https://online.tugraz.at/tug_online/ris.einzelraum?raumkey=1234', $room->getUrl());

        $room = $page->getItems()[1];
        $this->assertSame('1235', $room->getIdentifier());
        $this->assertSame('TECHN. TEST NORD', $room->getAdditionalInfo());
        $this->assertSame('Testgasse 4, 1.Obergeschoß', $room->getAddress());
        $this->assertSame('IE01234', $room->getCode());
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
        $page = $this->api->Room()->getRooms(['perPage' => 1, 'page' => 1]);
        $this->assertInstanceOf(Page::class, $page);
        $this->assertSame($page->getCurrentPageNumber(), 1);
        $this->assertSame($page->getMaxNumItemsPerPage(), 1);
        $this->assertCount(1, $page->getItems());

        $room = $page->getItems()[0];
        $this->assertSame('1234', $room->getIdentifier());
        $this->assertSame('Unit Test Projekt', $room->getAdditionalInfo());
        $this->assertSame('Teststraße 24, Erdgeschoß', $room->getAddress());
        $this->assertSame('IEEG123', $room->getCode());
        $this->assertSame('29', $room->getPurposeID());
        $this->assertSame(42.42, $room->getFloorSize());
        $this->assertSame('https://online.tugraz.at/tug_online/ris.einzelraum?raumkey=1234', $room->getUrl());

        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/rooms_response_1.xml')),
        ]);

        // explicit full pagination of a page 2 with 1 item
        $page = $this->api->Room()->getRooms(['perPage' => 1, 'page' => 2]);
        $this->assertInstanceOf(Page::class, $page);
        $this->assertSame($page->getCurrentPageNumber(), 2);
        $this->assertSame($page->getMaxNumItemsPerPage(), 1);
        $this->assertCount(1, $page->getItems());

        $room = $page->getItems()[0];
        $this->assertSame('1235', $room->getIdentifier());
        $this->assertSame('TECHN. TEST NORD', $room->getAdditionalInfo());
        $this->assertSame('Testgasse 4, 1.Obergeschoß', $room->getAddress());
        $this->assertSame('IE01234', $room->getCode());
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
        $page = $this->api->Room()->getRooms(['perPage' => 1, 'page' => 1]);
        $this->assertInstanceOf(Page::class, $page);
        $this->assertSame($page->getCurrentPageNumber(), 1);
        $this->assertSame($page->getMaxNumItemsPerPage(), 1);
        $this->assertCount(1, $page->getItems());

        $room = $page->getItems()[0];
        $this->assertSame('1234', $room->getIdentifier());
        $this->assertSame('Unit Test Projekt', $room->getAdditionalInfo());
        $this->assertSame('Teststraße 24, Erdgeschoß', $room->getAddress());
        $this->assertSame('IEEG123', $room->getCode());
        $this->assertSame('29', $room->getPurposeID());
        $this->assertSame(42.42, $room->getFloorSize());
        $this->assertSame('https://online.tugraz.at/tug_online/ris.einzelraum?raumkey=1234', $room->getUrl());

        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/rooms_response_1.xml')),
        ]);

        // partial pagination of a page 2 with 1 item
        $page = $this->api->Room()->getRooms(['perPage' => 1, 'page' => 2]);
        $this->assertInstanceOf(Page::class, $page);
        $this->assertSame($page->getCurrentPageNumber(), 2);
        $this->assertSame($page->getMaxNumItemsPerPage(), 1);
        $this->assertCount(1, $page->getItems());

        $room = $page->getItems()[0];
        $this->assertSame('1235', $room->getIdentifier());
        $this->assertSame('TECHN. TEST NORD', $room->getAdditionalInfo());
        $this->assertSame('Testgasse 4, 1.Obergeschoß', $room->getAddress());
        $this->assertSame('IE01234', $room->getCode());
        $this->assertSame('14', $room->getPurposeID());
        $this->assertSame(51.59, $room->getFloorSize());
        $this->assertSame('https://online.tugraz.at/tug_online/ris.einzelraum?raumkey=1235', $room->getUrl());
    }

    /**
     * Search filters: Pass, if ANY of the given search filters passes or if NONE is given.
     *
     * @throws ApiException
     */
    public function testGetRoomsFilters()
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/rooms_response_1.xml')),
        ]);

        // case-insensitive name search filter only with 1 case-insensitive match => 1 result
        $options = [];
        ResourceApi::addFilter($options, RoomData::CODE_ATTRIBUTE, Filters::CONTAINS_CI_OPERATOR, 'iee', Filters::LOGICAL_OR_OPERATOR);
        $page = $this->api->Room()->getRooms($options);
        $this->assertInstanceOf(Page::class, $page);
        $this->assertCount(1, $page->getItems());

        $room = $page->getItems()[0];
        $this->assertSame('1234', $room->getIdentifier());
        $this->assertSame('Unit Test Projekt', $room->getAdditionalInfo());
        $this->assertSame('Teststraße 24, Erdgeschoß', $room->getAddress());
        $this->assertSame('IEEG123', $room->getCode());
        $this->assertSame('29', $room->getPurposeID());
        $this->assertSame(42.42, $room->getFloorSize());
        $this->assertSame('https://online.tugraz.at/tug_online/ris.einzelraum?raumkey=1234', $room->getUrl());

        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/rooms_response_1.xml')),
        ]);

        // / case-sensitive name search filter only with 1 case-insensitive match => 0 result
        $options = [];
        ResourceApi::addFilter($options, RoomData::CODE_ATTRIBUTE, Filters::CONTAINS_OPERATOR, 'iee', Filters::LOGICAL_OR_OPERATOR);
        $page = $this->api->Room()->getRooms($options);
        $this->assertInstanceOf(Page::class, $page);
        $this->assertCount(0, $page->getItems());

        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/rooms_response_1.xml')),
        ]);

        // additional info search filter only with 1 match => 1 result
        $options = [];
        ResourceApi::addFilter($options, RoomData::ADDITIONAL_INFO_ATTRIBUTE, Filters::CONTAINS_OPERATOR, 'NORD', Filters::LOGICAL_OR_OPERATOR);
        $page = $this->api->Room()->getRooms($options);
        $this->assertInstanceOf(Page::class, $page);
        $this->assertCount(1, $page->getItems());

        $room = $page->getItems()[0];
        $this->assertSame('1235', $room->getIdentifier());
        $this->assertSame('TECHN. TEST NORD', $room->getAdditionalInfo());
        $this->assertSame('Testgasse 4, 1.Obergeschoß', $room->getAddress());
        $this->assertSame('IE01234', $room->getCode());
        $this->assertSame('14', $room->getPurposeID());
        $this->assertSame(51.59, $room->getFloorSize());
        $this->assertSame('https://online.tugraz.at/tug_online/ris.einzelraum?raumkey=1235', $room->getUrl());

        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/rooms_response_1.xml')),
        ]);

        // name filter only with no match => no results
        $options = [];
        ResourceApi::addFilter($options, RoomData::CODE_ATTRIBUTE, Filters::CONTAINS_OPERATOR, '_not_to_be_found_', Filters::LOGICAL_OR_OPERATOR);
        $page = $this->api->Room()->getRooms($options);
        $this->assertInstanceOf(Page::class, $page);
        $this->assertCount(0, $page->getItems());

        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/rooms_response_1.xml')),
        ]);

        // no search filters => all results
        $options = [];
        ResourceApi::addFilter($options, RoomData::CODE_ATTRIBUTE, Filters::CONTAINS_OPERATOR, '', Filters::LOGICAL_OR_OPERATOR);
        ResourceApi::addFilter($options, RoomData::ADDITIONAL_INFO_ATTRIBUTE, Filters::CONTAINS_OPERATOR, '', Filters::LOGICAL_OR_OPERATOR);
        $page = $this->api->Room()->getRooms($options);
        $this->assertInstanceOf(Page::class, $page);
        $this->assertCount(2, $page->getItems());

        $this->mockResponses([
            new Response(200, ['Content-Type' => 'text/xml;charset=utf-8'], file_get_contents(__DIR__.'/rooms_response_1.xml')),
        ]);

        // name search filter with no match, additional info search filter with 1 match => 1 result
        $options = [];
        ResourceApi::addFilter($options, RoomData::CODE_ATTRIBUTE, Filters::CONTAINS_OPERATOR, 'not to be found', Filters::LOGICAL_OR_OPERATOR);
        ResourceApi::addFilter($options, RoomData::ADDITIONAL_INFO_ATTRIBUTE, Filters::CONTAINS_OPERATOR, 'NORD', Filters::LOGICAL_OR_OPERATOR);
        $page = $this->api->Room()->getRooms($options);
        $this->assertInstanceOf(Page::class, $page);
        $this->assertCount(1, $page->getItems());
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
        $this->assertSame('IE01234', $room->getCode());
        $this->assertSame('14', $room->getPurposeID());
        $this->assertSame(51.59, $room->getFloorSize());
        $this->assertSame('https://online.tugraz.at/tug_online/ris.einzelraum?raumkey=1235', $room->getUrl());

        $this->assertSame('1235', $room->getData()[ResourceData::IDENTIFIER_ATTRIBUTE]);
        $this->assertSame('TECHN. TEST NORD', $room->getData()[RoomData::ADDITIONAL_INFO_ATTRIBUTE]);
        $this->assertSame('Testgasse 4, 1.Obergeschoß', $room->getData()[RoomData::ADDRESS_ATTRIBUTE]);
        $this->assertSame('IE01234', $room->getData()[RoomData::CODE_ATTRIBUTE]);
        $this->assertSame('14', $room->getData()[RoomData::PURPOSE_ID_ATTRIBUTE]);
        $this->assertSame('51.59', $room->getData()[RoomData::FLOOR_SIZE_ATTRIBUTE]);
        $this->assertSame('https://online.tugraz.at/tug_online/ris.einzelraum?raumkey=1235', $room->getData()[RoomData::URL_ATTRIBUTE]);
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
