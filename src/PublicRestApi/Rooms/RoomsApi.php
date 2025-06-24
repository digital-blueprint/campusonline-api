<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Rooms;

use Dbp\CampusonlineApi\Helpers\ApiException;
use Dbp\CampusonlineApi\PublicRestApi\Api;
use Dbp\CampusonlineApi\Rest\Tools;
use GuzzleHttp\Exception\GuzzleException;

class RoomsApi extends Api
{
    private const API_PATH = 'co/co-brm-core/facilities/api/rooms';

    private const ROOM_UID_QUERY_PARAMETER_NAME = 'room_uid';

    public function getRoomByIdentifier(string $identifier): RoomResource
    {
        try {
            return new RoomResource(Tools::decodeJsonResponse(
                $this->connection->getClient()->get(
                    self::API_PATH.'?'.http_build_query([
                        self::ROOM_UID_QUERY_PARAMETER_NAME => $identifier,
                    ])
                )));
        } catch (GuzzleException $guzzleException) {
            throw ApiException::fromGuzzleException($guzzleException);
        }
    }

    /**
     * @return iterable<RoomResource>
     */
    public function getRooms(int $firstItemIndex, int $maxNumItems, array $options = []): iterable
    {
        try {
            $response = $this->connection->getClient()->get(self::API_PATH.'?'.
                http_build_query(self::getPaginationQueryParameters($firstItemIndex, $maxNumItems)));
            foreach (Tools::decodeJsonResponse($response)['items'] ?? [] as $roomResourceData) {
                yield new RoomResource($roomResourceData);
            }
        } catch (GuzzleException $guzzleException) {
            throw ApiException::fromGuzzleException($guzzleException);
        }
    }
}
