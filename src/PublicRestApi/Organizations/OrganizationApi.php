<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Organizations;

use Dbp\CampusonlineApi\PublicRestApi\AbstractApi;
use Dbp\CampusonlineApi\PublicRestApi\CursorBasedResourcePage;

class OrganizationApi extends AbstractApi
{
    public const INCLUDE_CONTACT_INFO_QUERY_PARAMETER = 'include_contact';

    private const API_PATH = Common::API_PATH.'/organisations';

    private const UIDS_QUERY_PARAMETER = 'uids';

    public function getOrganizationByIdentifier(string $identifier): OrganizationResource
    {
        $resource = $this->getResourceByIdentifierFromCollection(
            $identifier,
            self::UIDS_QUERY_PARAMETER,
            self::API_PATH,
            OrganizationResource::class);
        assert($resource instanceof OrganizationResource);

        return $resource;
    }

    /**
     * @return iterable<OrganizationResource>
     */
    public function getOrganizationsOffsetBased(array $queryParameters = [],
        int $firstItemIndex = 0, int $maxNumItems = 30, array $options = []): iterable
    {
        return $this->getResourcesOffsetBased(
            self::API_PATH, OrganizationResource::class,
            $queryParameters, $firstItemIndex, $maxNumItems);
    }

    public function getOrganizationsCursorBased(array $queryParameters = [],
        ?string $cursor = null, int $maxNumItems = 30, array $options = []): CursorBasedResourcePage
    {
        return $this->getResourcesCursorBased(
            self::API_PATH, OrganizationResource::class,
            $queryParameters, $cursor, $maxNumItems);
    }
}
