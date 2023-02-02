<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Room;

use Dbp\CampusonlineApi\Helpers\Page;
use Dbp\CampusonlineApi\LegacyWebService\ApiException;
use Dbp\CampusonlineApi\LegacyWebService\Connection;
use Dbp\CampusonlineApi\LegacyWebService\Organization\OrganizationUnitApi;
use Dbp\CampusonlineApi\LegacyWebService\ResourceApi;
use Dbp\CampusonlineApi\LegacyWebService\ResourceData;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class RoomApi extends ResourceApi implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** Request attributes: */
    private const COLLECTION_URI = 'ws/webservice_v1.0/rdm/rooms/xml';
    private const ITEM_URI = 'ws/webservice_v1.0/rdm/room/xml';
    private const ROOM_ID_PARAMETER_NAME = 'roomID';

    /** Response attributes: */
    private const ROOM_RESOURCE_XML_PATH = './/cor:resource[@cor:typeID="room"]';
    private const ROOM_IDENTIFIER_XML_PATH = './cor:description/cor:attribute[@cor:attrID="roomID"]';
    private const ROOM_NAME_XML_PATH = './cor:description/cor:attribute[@cor:attrID="roomCode"]';

    private const ATTRIBUTE_NAME_TO_XPATH_MAPPING = [
        ResourceData::IDENTIFIER_ATTRIBUTE => self::ROOM_IDENTIFIER_XML_PATH,
        RoomData::NAME_ATTRIBUTE => self::ROOM_NAME_XML_PATH,
        RoomData::ADDITIONAL_INFO_ATTRIBUTE => './cor:description/cor:attribute[@cor:attrID="additionalInformation"]',
        RoomData::ADDRESS_ATTRIBUTE => './cor:description/cor:attribute[@cor:attrID="address"]',
        RoomData::URL_ATTRIBUTE => './cor:description/cor:attribute[@cor:attrID="address"]/@cor:attrAltUrl',
        RoomData::FLOOR_SIZE_ATTRIBUTE => './cor:description/cor:attribute[@cor:attrID="area"]',
        RoomData::PURPOSE_ID_ATTRIBUTE => './cor:description/cor:attribute[@cor:attrID="purposeID"]',
        RoomData::PURPOSE_ATTRIBUTE => './cor:description/cor:attribute[@cor:attrID="purpose"]',
    ];

    public function __construct(Connection $connection, string $rootOrgUnitId)
    {
        parent::__construct($connection, $rootOrgUnitId, self::ROOM_RESOURCE_XML_PATH);
    }

    public function checkConnection()
    {
        // To check if the API can respond with a proper error
        // NOTE: room API returns 404 if no id is specified, where other APIs (course, organization, ...) return 400
        $this->expectGetError(self::ITEM_URI, [], 404);
        // To check that the token is valid (otherwise we get 401)
        $this->expectGetError(self::ITEM_URI, [self::ROOM_ID_PARAMETER_NAME => ''], 404);
    }

    /**
     * @throws ApiException
     */
    public function getRoomById(string $identifier, array $options = []): RoomData
    {
        if ($identifier === '') {
            throw new ApiException("identifier mustn't be empty");
        }

        ResourceApi::addIdFilter($options, $identifier);

        $paginator = $this->getRoomsInternal($options);

        $roomItems = $paginator->getItems();
        if (empty($roomItems)) {
            throw new ApiException("response doesn't contain room with ID ".$identifier, 404, true);
        }
        assert(count($roomItems) === 1);

        return $roomItems[0];
    }

    /**
     * @throws ApiException
     */
    public function getRooms(array $options = []): Page
    {
        return $this->getRoomsInternal($options);
    }

    /**
     * Currently all rooms are requested and cached. Requested rooms are then fetched from the XML response.
     *
     * @throws ApiException
     */
    private function getRoomsInternal(array $options): Page
    {
        $parameters = [];
        $parameters[OrganizationUnitApi::ORG_UNIT_ID_PARAMETER_NAME] = $this->rootOrgUnitId;

        return $this->getResourcesInternal(self::COLLECTION_URI, $parameters, $options);
    }

    protected function createResource(\SimpleXMLElement $node): ResourceData
    {
        return new RoomData();
    }

    protected function getAttributeNameToXpathExpressionMapping(): array
    {
        return self::ATTRIBUTE_NAME_TO_XPATH_MAPPING;
    }
}
