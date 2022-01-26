<?php

namespace Dbp\CampusonlineApi\LegacyWebService\Room;

use Dbp\CampusonlineApi\LegacyWebService\Api;
use Dbp\CampusonlineApi\LegacyWebService\Connection;
use Dbp\CampusonlineApi\Rest\ApiException;
use SimpleXMLElement;

class RoomApi
{
    private const ROOT_ORG_UNIT_PARAMETER_NAME = 'orgUnitID';
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
    public function getRoomById(string $identifier, array $options = []): ?RoomData
    {
        $rooms = $this->getRoomsInternal($identifier, $options);
        return empty($rooms) ? null : $rooms[0];
    }

    /**
     * @return RoomData[]
     * @throws ApiException
     */
    public function getRooms(array $options = []): array
    {
        return $this->getRoomsInternal(null, $options);
    }

    /**
     * @return RoomData[]
     * @throws ApiException
     */
    private function getRoomsInternal(?string $roomId, array $options): array
    {
        $parameters = [];
        $parameters[Api::LANGUAGE_PARAMETER_NAME] = $options[Api::LANGUAGE_PARAMETER_NAME] ?? Api::DEFAULT_LANGUAGE;
        $parameters[self::ROOT_ORG_UNIT_PARAMETER_NAME] = $this->rootOrgUnitId;

        $responseBody = $this->connection->get(self::URI, $parameters);

        return $this->parseRoomsDataResponse($responseBody, $roomId);
    }

    /**
     * @return RoomData[]
     * @throws ApiException
     */
    private function parseRoomsDataResponse(string $responseBody, ?string $roomId): array
    {
        $rooms = [];
        try {
            $xml = new SimpleXMLElement($responseBody);
        } catch (\Exception $e) {
            throw new ApiException("response body is not in valid XML format");
        }
        $units = $xml->xpath('.//cor:resource');

        foreach ($units as $unit) {
            $wasRoomFound = false;
            $identifier = trim((string) ($unit->xpath('./cor:description/cor:attribute[@cor:attrID="roomID"]')[0] ?? ''));
            if ($identifier === '') {
                continue;
            } else if ($identifier === $roomId) {
                    $wasRoomFound = true;
            } else if (strlen($roomId) > 0) {
                continue;
            }

            $address = trim((string) ($unit->xpath('./cor:description/cor:attribute[@cor:attrID="address"]')[0] ?? ''));
            $url = trim((string) ($unit->xpath('./cor:description/cor:attribute[@cor:attrID="address"]/@cor:attrAltUrl')[0] ?? ''));
            $floorSize = trim((string) ($unit->xpath('./cor:description/cor:attribute[@cor:attrID="area"]')[0] ?? ''));
            $description = trim((string) ($unit->xpath('./cor:description/cor:attribute[@cor:attrID="purpose"]')[0] ?? ''));
            $permittedUsage = trim((string) ($unit->xpath('./cor:description/cor:attribute[@cor:attrID="purposeID"]')[0] ?? ''));
            $name = trim((string) ($unit->xpath('./cor:description/cor:attribute[@cor:attrID="additionalInformation"]')[0] ?? ''));
            $alternateName = trim((string) ($unit->xpath('./cor:description/cor:attribute[@cor:attrID="roomCode"]')[0] ?? ''));
            $room = new RoomData();
            $room->setIdentifier($identifier);
            $room->setAddress($address);
            $room->setUrl($url);
            $room->setFloorSize($floorSize);
            $room->setDescription($description);
            $room->setPermittedUsage($permittedUsage);
            $room->setName($name);
            $room->setAlternateName($alternateName);

            $rooms[$identifier] = $room;

            if ($wasRoomFound) {
                break;
            }
        }

        return array_values($rooms);
    }
}
