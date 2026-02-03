<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Accounts;

use Dbp\CampusonlineApi\Helpers\ApiException;
use Dbp\CampusonlineApi\PublicRestApi\AbstractApi;
use Dbp\CampusonlineApi\PublicRestApi\CursorBasedResourcePage;

class UserApi extends AbstractApi
{
    public const PERSON_UID_QUERY_PARAMETER_NAME = 'person_uid';
    public const ACCOUNT_TYPE_KEY_QUERY_PARAMETER_NAME = 'account_type_key';
    public const ACCOUNT_STATUS_KEY_QUERY_PARAMETER_NAME = 'account_status_key';

    private const API_PATH = Common::API_PATH.'/users/search';

    public function getUserByPersonUid(string $personUid, array $options = []): UserResource
    {
        $users = $this->getResourcesOffsetBased(self::API_PATH,
            UserResource::class, [
                self::PERSON_UID_QUERY_PARAMETER_NAME => $personUid,
            ]);
        if (empty($users)) {
            throw new ApiException('user not found', ApiException::HTTP_NOT_FOUND);
        }

        return $users[0];
    }

    public function getUsersCursorBased(array $queryParameters = [],
        ?string $cursor = null, int $maxNumItems = 30, array $options = []): CursorBasedResourcePage
    {
        return $this->getResourcesCursorBased(self::API_PATH,
            UserResource::class, $queryParameters, $cursor, $maxNumItems,
            options: [self::USE_POST_OPTION => true]);
    }

    public function getUsersOffsetBased(array $queryParameters = [],
        int $firstItemIndex = 0, int $maxNumItems = 30, array $options = []): array
    {
        return $this->getResourcesOffsetBased(self::API_PATH,
            UserResource::class, $queryParameters, $firstItemIndex, $maxNumItems,
            options: [self::USE_POST_OPTION => true]);
    }
}
