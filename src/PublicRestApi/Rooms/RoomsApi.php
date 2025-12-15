<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Rooms;

use Dbp\CampusonlineApi\PublicRestApi\AbstractApi;

class RoomsApi extends AbstractApi
{
    private const ROOMS_API_PATH = Common::API_PATH.'/rooms';

    private const ROOM_UID_QUERY_PARAMETER_NAME = 'room_uid';

    public function getRoomByIdentifier(string $identifier): RoomResource
    {
        $resource = $this->getResourceByIdentifierFromCollection(
            $identifier,
            self::ROOM_UID_QUERY_PARAMETER_NAME,
            self::ROOMS_API_PATH,
            RoomResource::class);
        assert($resource instanceof RoomResource);

        return $resource;
    }

    /**
     * @return iterable<RoomResource>
     */
    public function getRooms(array $queryParameters = [],
        int $firstItemIndex = 0, int $maxNumItems = 30, array $options = []): iterable
    {
        return $this->getResourcesOffsetBased(
            self::ROOMS_API_PATH, RoomResource::class,
            $queryParameters, $firstItemIndex, $maxNumItems);
    }
}
