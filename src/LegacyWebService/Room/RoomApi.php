<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Room;

use Dbp\CampusonlineApi\Helpers\Filters;
use Dbp\CampusonlineApi\Helpers\Paginator;
use Dbp\CampusonlineApi\LegacyWebService\ApiException;
use Dbp\CampusonlineApi\LegacyWebService\Connection;
use Dbp\CampusonlineApi\LegacyWebService\Organization\OrganizationUnitApi;
use Dbp\CampusonlineApi\LegacyWebService\ResourceApi;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SimpleXMLElement;

class RoomApi extends ResourceApi implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const URI = 'ws/webservice_v1.0/rdm/rooms/xml';

    private const ROOM_RESOURCE_XML_PATH = './/cor:resource[@cor:typeID="room"]';
    private const ROOM_IDENTIFIER_XML_PATH = './cor:description/cor:attribute[@cor:attrID="roomID"]';
    private const ROOM_NAME_XML_PATH = './cor:description/cor:attribute[@cor:attrID="roomCode"]';

    public function __construct(Connection $connection, string $rootOrgUnitId)
    {
        parent::__construct($connection, $rootOrgUnitId,
            self::ROOM_RESOURCE_XML_PATH, self::ROOM_IDENTIFIER_XML_PATH);
    }

    /**
     * @throws ApiException
     */
    public function getRoomById(string $identifier, array $options = []): RoomData
    {
        if (strlen($identifier) === 0) {
            throw new ApiException("identifier mustn't be empty");
        }

        $options[Filters::IDENTIFIERS_FILTER] = [$identifier];

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
    public function getRooms(array $options = []): Paginator
    {
        return $this->getRoomsInternal($options);
    }

    /**
     * Currently all rooms are requested and cached. Requested rooms are then fetched from the XML response.
     *
     * @throws ApiException
     */
    private function getRoomsInternal(array $options): Paginator
    {
        $parameters = [];
        $parameters[OrganizationUnitApi::ORG_UNIT_ID_PARAMETER_NAME] = $this->rootOrgUnitId;

        return $this->getResourcesInternal(self::URI, $parameters, $options);
    }

    protected function createResource(SimpleXMLElement $node, string $identifier): object
    {
        $name = $this->getResourceName($node);
        $additionalInfo = self::getRoomAdditionalInfo($node);
        $address = self::getResourcePropertyOrEmptyString($node, './cor:description/cor:attribute[@cor:attrID="address"]');
        $url = self::getResourcePropertyOrEmptyString($node, './cor:description/cor:attribute[@cor:attrID="address"]/@cor:attrAltUrl');
        $floorSize = self::getResourcePropertyOrEmptyString($node, './cor:description/cor:attribute[@cor:attrID="area"]');
        $purposeID = self::getResourcePropertyOrEmptyString($node, './cor:description/cor:attribute[@cor:attrID="purposeID"]');
        $purpose = self::getResourcePropertyOrEmptyString($node, './cor:description/cor:attribute[@cor:attrID="purpose"]');

        $room = new RoomData();
        $room->setIdentifier($identifier);
        $room->setName($name);
        $room->setAddress($address);
        $room->setUrl($url);
        $room->setFloorSize(floatval($floorSize));
        $room->setPurposeId($purposeID);
        $room->setPurpose($purpose);
        $room->setAdditionalInfo($additionalInfo);

        return $room;
    }

    /**
     * Checks whether the room passes the given search filters. Performs a partial, case-insensitive text search.
     * Passes if ANY of the given search filters passes or if NONE is given.
     */
    protected function passesSearchFilter(SimpleXMLElement $node, array $options): bool
    {
        $additionalInfoSearchFilter = $options[RoomData::ADDITIONAL_INFO_SEARCH_FILTER_NAME] ?? '';

        return parent::passesSearchFilter($node, $options) ||
            ($additionalInfoSearchFilter !== '' && stripos(self::getRoomAdditionalInfo($node), $additionalInfoSearchFilter) !== false);
    }

    protected function isSearchFilterActive(array $options): bool
    {
        $additionalInfoSearchFilter = $options[RoomData::ADDITIONAL_INFO_SEARCH_FILTER_NAME] ?? '';

        return parent::isSearchFilterActive($options) || $additionalInfoSearchFilter !== '';
    }

    protected function getResourceName(SimpleXMLElement $node): string
    {
        return self::getResourcePropertyOrEmptyString($node, self::ROOM_NAME_XML_PATH);
    }

    private static function getRoomAdditionalInfo(SimpleXMLElement $node): string
    {
        return self::getResourcePropertyOrEmptyString($node, './cor:description/cor:attribute[@cor:attrID="additionalInformation"]');
    }

    public function checkConnection()
    {
        // TODO: Implement checkConnection() method.
    }
}
