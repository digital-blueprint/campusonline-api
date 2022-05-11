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

        // count on php arrays seems to be O(1) (https://stackoverflow.com/questions/5835241/is-phps-count-function-o1-or-on-for-arrays, so we always return a full paginator.
        // partial pagination would make sense for a streaming XML parser, though
        $totalNumItems = count($nodes);
        if ($totalNumItems > 0) {
            $firstItemIndex = 0;
            $lastItemIndex = $totalNumItems - 1;

            if (!$isIdRequested) {
                Pagination::getCurrentPageIndicesFull($totalNumItems, $options, $firstItemIndex, $lastItemIndex);
            }

            for ($index = $firstItemIndex; $index <= $lastItemIndex; ++$index) {
                $node = $nodes[$index];
                $identifier = trim((string) ($node->xpath('./cor:description/cor:attribute[@cor:attrID="roomID"]')[0] ?? ''));
                if ($identifier === '') {
                    throw new ApiException('roomID missing in CO room resource');
                }

                $wasIdFound = false;
                if ($isIdRequested) {
                    if ($identifier === $requestedId) {
                        $wasIdFound = true;
                    } else {
                        continue;
                    }
                }

                $roomCode = trim((string) ($node->xpath('./cor:description/cor:attribute[@cor:attrID="roomCode"]')[0] ?? ''));
                $address = trim((string) ($node->xpath('./cor:description/cor:attribute[@cor:attrID="address"]')[0] ?? ''));
                $url = trim((string) ($node->xpath('./cor:description/cor:attribute[@cor:attrID="address"]/@cor:attrAltUrl')[0] ?? ''));
                $floorSize = trim((string) ($node->xpath('./cor:description/cor:attribute[@cor:attrID="area"]')[0] ?? ''));
                $purposeID = trim((string) ($node->xpath('./cor:description/cor:attribute[@cor:attrID="purposeID"]')[0] ?? ''));
                $purpose = trim((string) ($node->xpath('./cor:description/cor:attribute[@cor:attrID="purpose"]')[0] ?? ''));
                $additionalInfo = trim((string) ($node->xpath('./cor:description/cor:attribute[@cor:attrID="additionalInformation"]')[0] ?? ''));

                $room = new RoomData();
                $room->setIdentifier($identifier);
                $room->setName($roomCode);
                $room->setAddress($address);
                $room->setUrl($url);
                $room->setFloorSize(floatval($floorSize));
                $room->setPurposeId($purposeID);
                $room->setPurpose($purpose);
                $room->setAdditionalInfo($additionalInfo);

                $rooms[] = $room;

                if ($wasIdFound) {
                    break;
                }
            }
        }

        return Pagination::createFullPaginator($rooms, $isIdRequested ? count($rooms) : $totalNumItems, $options);
    }
}
