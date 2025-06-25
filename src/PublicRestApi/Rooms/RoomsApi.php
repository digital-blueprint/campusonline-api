<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Rooms;

use Dbp\CampusonlineApi\Helpers\ApiException;
use Dbp\CampusonlineApi\PublicRestApi\Api;
use Dbp\CampusonlineApi\Rest\Tools;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class RoomsApi extends Api
{
    private const API_PATH = 'co/co-brm-core/facilities/api/rooms';

    private const ROOM_UID_QUERY_PARAMETER_NAME = 'room_uid';

    public function getRoomByIdentifier(string $identifier): RoomResource
    {
        try {
            $roomResources = iterator_to_array($this->getRoomsFromResponse(
                $this->connection->getClient()->get(
                    self::API_PATH.'?'.http_build_query([
                        self::ROOM_UID_QUERY_PARAMETER_NAME => $identifier,
                    ])
                )));
            if (empty($roomResources)) {
                throw new ApiException('room not found', ApiException::HTTP_NOT_FOUND, true);
            }

            return $roomResources[0];
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
            return $this->getRoomsFromResponse(
                $this->connection->getClient()->get(self::API_PATH.'?'.
                    http_build_query(self::getPaginationQueryParameters($firstItemIndex, $maxNumItems))));
        } catch (GuzzleException $guzzleException) {
            throw ApiException::fromGuzzleException($guzzleException);
        }
    }

    private function getRoomsFromResponse(ResponseInterface $response): iterable
    {
        foreach (Tools::decodeJsonResponse($response)['items'] ?? [] as $roomResourceData) {
            yield new RoomResource($roomResourceData);
        }
    }
}
