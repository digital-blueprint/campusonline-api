<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Rooms;

use Dbp\CampusonlineApi\PublicRestApi\AbstractApi;
use Dbp\CampusonlineApi\PublicRestApi\CursorBasedResourcePage;

class RoomApi extends AbstractApi
{
    private const API_PATH = Common::API_PATH.'/rooms';

    private const ROOM_UID_QUERY_PARAMETER_NAME = 'room_uid';

    public function getRoomByIdentifier(string $identifier): RoomResource
    {
        $resource = $this->getResourceByIdentifierFromCollection(
            $identifier,
            self::ROOM_UID_QUERY_PARAMETER_NAME,
            self::API_PATH,
            RoomResource::class);
        assert($resource instanceof RoomResource);

        return $resource;
    }

    /**
     * @return iterable<RoomResource>
     */
    public function getRoomsOffsetBased(array $queryParameters = [],
        int $firstItemIndex = 0, int $maxNumItems = 30, array $options = []): iterable
    {
        return $this->getResourcesOffsetBased(
            self::API_PATH, RoomResource::class,
            $queryParameters, $firstItemIndex, $maxNumItems);
    }

    public function getRoomsCursorBased(array $queryParameters = [],
        ?string $cursor = null, int $maxNumItems = 30, array $options = []): CursorBasedResourcePage
    {
        return $this->getResourcesCursorBased(
            self::API_PATH, RoomResource::class,
            $queryParameters, $cursor, $maxNumItems);
    }
}
