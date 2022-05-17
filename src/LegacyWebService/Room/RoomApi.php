<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Room;

use Dbp\CampusonlineApi\Helpers\Pagination;
use Dbp\CampusonlineApi\Helpers\Paginator;
use Dbp\CampusonlineApi\LegacyWebService\Api;
use Dbp\CampusonlineApi\LegacyWebService\ApiException;
use Dbp\CampusonlineApi\LegacyWebService\Connection;
use Dbp\CampusonlineApi\LegacyWebService\Organization\OrganizationUnitApi;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SimpleXMLElement;

class RoomApi implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    private const URI = 'ws/webservice_v1.0/rdm/rooms/xml';

    private $connection;
    private $rootOrgUnitId;

    public function __construct(Connection $connection, $rootOrgUnitId)
    {
        $this->connection = $connection;
        $this->rootOrgUnitId = $rootOrgUnitId;
    }

    /**
     * @throws ApiException
     */
    public function getRoomById(string $identifier, array $options = []): RoomData
    {
        if (strlen($identifier) === 0) {
            throw new ApiException("identifier mustn't be empty");
        }

        $paginator = $this->getRoomsInternal($identifier, $options);

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
        return $this->getRoomsInternal('', $options);
    }

    /**
     * Currently all rooms are requested and cached. Requested rooms are then fetched from the XML response.
     *
     * @throws ApiException
     */
    private function getRoomsInternal(string $roomId, array $options): Paginator
    {
        $parameters = [];
        $parameters[OrganizationUnitApi::ORG_UNIT_ID_PARAMETER_NAME] = $this->rootOrgUnitId;

        $responseBody = $this->connection->get(self::URI, $options[Api::LANGUAGE_PARAMETER_NAME] ?? '', $parameters);

        return $this->parseResponse($responseBody, $roomId, $options);
    }

    /**
     * @throws ApiException
     */
    private function parseResponse(string $responseBody, string $requestedId, array $options): Paginator
    {
        $rooms = [];
        $isIdRequested = $requestedId !== '';

        try {
            $xml = new SimpleXMLElement($responseBody);
        } catch (\Exception $e) {
            throw new ApiException('response body is not in valid XML format');
        }
        $nodes = $xml->xpath('.//cor:resource[@cor:typeID="room"]');

        $firstMatchingItemsIndex = 0;
        $isPartialPagination = false;
        $nameSearchFilter = '';
        $additionalInfoSearchFilter = '';
        $isSearchFilterActive = false;

        if (!$isIdRequested) {
            $firstMatchingItemsIndex = Pagination::getCurrentPageStartIndex($options);
            $isPartialPagination = Pagination::isPartial($options);
            $nameSearchFilter = $options[RoomData::NAME_SEARCH_FILTER_NAME] ?? '';
            $additionalInfoSearchFilter = $options[RoomData::ADDITIONAL_INFO_SEARCH_FILTER_NAME] ?? '';
            $isSearchFilterActive = $nameSearchFilter !== '' || $additionalInfoSearchFilter !== '';
        }

        $totalNumItems = count($nodes);
        $numItemsPerPage = Pagination::getNumItemsPerPage($options);
        $matchingItemCount = 0;

        for ($nodeIndex = 0; $nodeIndex < $totalNumItems; ++$nodeIndex) {
            $node = $nodes[$nodeIndex];

            $identifier = trim((string) ($node->xpath('./cor:description/cor:attribute[@cor:attrID="roomID"]')[0] ?? ''));
            if ($identifier === '') {
                throw new ApiException('roomID missing in Campusonline room resource');
            }

            $name = null;
            $additionalInfo = null;
            $isMatch = false;

            if ($isIdRequested) {
                if ($identifier === $requestedId) {
                    $isMatch = true;
                }
            } else {
                $isMatch = !$isSearchFilterActive || self::passesSearchFilter($node, $nameSearchFilter, $additionalInfoSearchFilter, $name, $additionalInfo);
            }

            if ($isMatch) {
                ++$matchingItemCount;
                if ($isIdRequested || ($matchingItemCount > $firstMatchingItemsIndex && (!$numItemsPerPage || count($rooms) < $numItemsPerPage))) {
                    $rooms[] = self::createRoom($node, $identifier, $name, $additionalInfo);

                    $done = false;
                    if ($isIdRequested) {
                        $done = true;
                    } elseif (count($rooms) === $numItemsPerPage) {
                        if ($isPartialPagination) {
                            $done = true;
                        } elseif (!$isSearchFilterActive) {
                            $done = true;
                            $matchingItemCount = $totalNumItems;
                        }
                    }
                    if ($done) {
                        break;
                    }
                }
            }
        }

        if ($isPartialPagination) {
            return Pagination::createPartialPaginator($rooms, $options);
        } else {
            return Pagination::createFullPaginator($rooms, $matchingItemCount, $options);
        }
    }

    /**
     * Checks whether the room passes the given search filters. Performs a partial, case-insensitive text search.
     * Passes if ANY of the given search filters passes or if NONE is given.
     */
    private static function passesSearchFilter(SimpleXMLElement $roomNode, string $nameSearchFilter, string $additionalInfoSearchFilter, ?string &$name, ?string &$additionalInfo): bool
    {
        $isNameSearchFilterActive = $nameSearchFilter !== '';
        $isAdditionalInfoSearchFilterActive = $additionalInfoSearchFilter !== '';

        return
            ($isNameSearchFilterActive && stripos($name = self::getRoomName($roomNode), $nameSearchFilter) !== false) ||
            ($isAdditionalInfoSearchFilterActive && stripos($additionalInfo = self::getRoomAdditionalInfo($roomNode), $additionalInfoSearchFilter) !== false) ||
            (!$isNameSearchFilterActive && !$isAdditionalInfoSearchFilterActive);
    }

    private static function getRoomName(SimpleXMLElement $roomNode): string
    {
        return trim((string) ($roomNode->xpath('./cor:description/cor:attribute[@cor:attrID="roomCode"]')[0] ?? ''));
    }

    private static function getRoomAdditionalInfo(SimpleXMLElement $roomNode): string
    {
        return trim((string) ($roomNode->xpath('./cor:description/cor:attribute[@cor:attrID="additionalInformation"]')[0] ?? ''));
    }

    private static function createRoom(SimpleXMLElement $roomNode, string $identifier, ?string $name, ?string $additionalInfo): RoomData
    {
        if (!$name) {
            $name = self::getRoomName($roomNode);
        }
        if (!$additionalInfo) {
            $additionalInfo = self::getRoomAdditionalInfo($roomNode);
        }
        $address = trim((string) ($roomNode->xpath('./cor:description/cor:attribute[@cor:attrID="address"]')[0] ?? ''));
        $url = trim((string) ($roomNode->xpath('./cor:description/cor:attribute[@cor:attrID="address"]/@cor:attrAltUrl')[0] ?? ''));
        $floorSize = trim((string) ($roomNode->xpath('./cor:description/cor:attribute[@cor:attrID="area"]')[0] ?? ''));
        $purposeID = trim((string) ($roomNode->xpath('./cor:description/cor:attribute[@cor:attrID="purposeID"]')[0] ?? ''));
        $purpose = trim((string) ($roomNode->xpath('./cor:description/cor:attribute[@cor:attrID="purpose"]')[0] ?? ''));

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
}
