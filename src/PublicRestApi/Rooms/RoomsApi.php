<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Rooms;

use Dbp\CampusonlineApi\Helpers\ApiException;
use Dbp\CampusonlineApi\PublicRestApi\AbstractApi;
use Dbp\CampusonlineApi\Rest\Tools;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class RoomsApi extends AbstractApi
{
    private const API_PATH = 'co/co-brm-core/facilities/api';
    private const ROOMS_API_PATH = self::API_PATH.'/rooms';

    private const ROOM_UID_QUERY_PARAMETER_NAME = 'room_uid';

    public function getRoomByIdentifier(int $identifier): RoomResource
    {
        try {
            $roomResources = iterator_to_array($this->getRoomsFromResponse(
                $this->connection->getClient()->get(
                    self::ROOMS_API_PATH.'?'.http_build_query([
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
    public function getRooms(int $firstItemIndex = 0, int $maxNumItems = 30, array $options = []): iterable
    {
        try {
            // WORKAROUND: CO ignores limit=0
            if ($maxNumItems === 0) {
                return [];
            } else {
                return $this->getRoomsFromResponse(
                    $this->connection->getClient()->get(self::ROOMS_API_PATH.'?'.
                        http_build_query(self::getOffsetBasedPaginationQueryParameters($firstItemIndex, $maxNumItems))));
            }
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
