<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Organizations;

use Dbp\CampusonlineApi\PublicRestApi\AbstractApi;
use Dbp\CampusonlineApi\PublicRestApi\CursorBasedResourcePage;

class PersonOrganisationApi extends AbstractApi
{
    public const PERSON_UID_QUERY_PARAMETER_NAME = 'person_uid';
    public const ORGANISATION_UID_QUERY_PARAMETER_NAME = 'organisation_uid';

    private const API_PATH = Common::API_PATH.'/person-organisations';

    public function getPersonOrganisationsCursorBased(array $queryParameters = [],
        ?string $cursor = null, int $maxNumItems = 30, array $options = []): CursorBasedResourcePage
    {
        return $this->getResourcesCursorBased(
            self::API_PATH,
            PersonOrganisationResource::class,
            $queryParameters,
            $cursor,
            $maxNumItems
        );
    }

    /**
     * @return iterable<PersonOrganisationResource>
     */
    public function getPersonOrganisationsOffsetBased(array $queryParameters = [],
        int $firstItemIndex = 0, int $maxNumItems = 30, array $options = []): iterable
    {
        return $this->getResourcesOffsetBased(
            self::API_PATH,
            PersonOrganisationResource::class,
            $queryParameters,
            $firstItemIndex,
            $maxNumItems
        );
    }
}
