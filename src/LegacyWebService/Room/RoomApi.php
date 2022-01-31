<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Room;

use Dbp\CampusonlineApi\LegacyWebService\Api;
use Dbp\CampusonlineApi\LegacyWebService\Connection;
use Dbp\CampusonlineApi\Rest\ApiException;
use SimpleXMLElement;

class RoomApi
{
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
        assert(count($rooms) <= 1);

        return empty($rooms) ? null : $rooms[0];
    }

    /**
     * @return RoomData[]
     *
     * @throws ApiException
     */
    public function getRooms(array $options = []): array
    {
        return $this->getRoomsInternal('', $options);
    }

    /**
     * Currently all rooms are requested and cached. Requested rooms are then fetched from the XML response.
     *
     * @return RoomData[]
     *
     * @throws ApiException
     */
    private function getRoomsInternal(string $roomId, array $options): array
    {
        $parameters = [];
        $parameters[Api::ORG_UNIT_ID_PARAMETER_NAME] = $this->rootOrgUnitId;

        $responseBody = $this->connection->get(self::URI, $options[Api::LANGUAGE_PARAMETER_NAME] ?? '', $parameters);

        return $this->parseResponse($responseBody, $roomId);
    }

    /**
     * @return RoomData[]
     *
     * @throws ApiException
     */
    private function parseResponse(string $responseBody, string $requestedId): array
    {
        $rooms = [];

        try {
            $xml = new SimpleXMLElement($responseBody);
        } catch (\Exception $e) {
            throw new ApiException('response body is not in valid XML format');
        }
        $nodes = $xml->xpath('.//cor:resource');

        foreach ($nodes as $node) {
            $identifier = trim((string) ($node->xpath('./cor:description/cor:attribute[@cor:attrID="roomID"]')[0] ?? ''));
            if ($identifier === '') {
                continue;
            }

            $wasIdFound = false;
            if ($requestedId !== '') {
                if ($identifier === $requestedId) {
                    $wasIdFound = true;
                } else {
                    continue;
                }
            }

            $address = trim((string) ($node->xpath('./cor:description/cor:attribute[@cor:attrID="address"]')[0] ?? ''));
            $url = trim((string) ($node->xpath('./cor:description/cor:attribute[@cor:attrID="address"]/@cor:attrAltUrl')[0] ?? ''));
            $floorSize = trim((string) ($node->xpath('./cor:description/cor:attribute[@cor:attrID="area"]')[0] ?? ''));
            $description = trim((string) ($node->xpath('./cor:description/cor:attribute[@cor:attrID="purpose"]')[0] ?? ''));
            $permittedUsage = trim((string) ($node->xpath('./cor:description/cor:attribute[@cor:attrID="purposeID"]')[0] ?? ''));
            $name = trim((string) ($node->xpath('./cor:description/cor:attribute[@cor:attrID="additionalInformation"]')[0] ?? ''));
            $alternateName = trim((string) ($node->xpath('./cor:description/cor:attribute[@cor:attrID="roomCode"]')[0] ?? ''));

            $room = new RoomData();
            $room->setIdentifier($identifier);
            $room->setAddress($address);
            $room->setUrl($url);
            $room->setFloorSize(floatval($floorSize));
            $room->setDescription($description);
            $room->setPermittedUsage($permittedUsage);
            $room->setName($name);
            $room->setAlternateName($alternateName);

            $rooms[] = $room;

            if ($wasIdFound) {
                break;
            }
        }

        return $rooms;
    }
}
