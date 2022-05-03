<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService\Room;

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

        $rooms = $this->getRoomsInternal($identifier, $options);
        if (empty($rooms)) {
            throw new ApiException("response doesn't contain room with ID ".$identifier, 404, true);
        }
        assert(count($rooms) === 1);

        return $rooms[0];
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
        $parameters[OrganizationUnitApi::ORG_UNIT_ID_PARAMETER_NAME] = $this->rootOrgUnitId;

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

        return $rooms;
    }
}
