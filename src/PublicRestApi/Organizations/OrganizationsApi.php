<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Organizations;

use Dbp\CampusonlineApi\PublicRestApi\AbstractApi;

class OrganizationsApi extends AbstractApi
{
    private const ORGANIZATIONS_API_PATH = Common::API_PATH.'/organisations';

    private const UIDS_QUERY_PARAMETER_NAME = 'uids';

    public function getOrganizationByIdentifier(string $identifier): OrganizationResource
    {
        $resource = $this->getResourceByIdentifierFromCollection(
            $identifier,
            self::UIDS_QUERY_PARAMETER_NAME,
            self::ORGANIZATIONS_API_PATH,
            OrganizationResource::class);
        assert($resource instanceof OrganizationResource);

        return $resource;
    }

    /**
     * @return iterable<OrganizationResource>
     */
    public function getOrganizations(int $firstItemIndex = 0, int $maxNumItems = 30, array $options = []): iterable
    {
        return $this->getResourcesOffsetBased(self::ORGANIZATIONS_API_PATH, OrganizationResource::class,
            firstItemIndex: $firstItemIndex,
            maxNumItems: $maxNumItems);
    }
}
