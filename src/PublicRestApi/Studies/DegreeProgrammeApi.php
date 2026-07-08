<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Studies;

use Dbp\CampusonlineApi\PublicRestApi\AbstractApi;
use Dbp\CampusonlineApi\PublicRestApi\CursorBasedResourcePage;

class DegreeProgrammeApi extends AbstractApi
{
    public const DEGREE_PROGRAMME_UID_QUERY_PARAMETER_NAME = 'degree_programme_uid';
    private const API_PATH = Common::API_PATH.'/degree-programmes';

    /**
     * @return iterable<DegreeProgrammeResource>
     */
    public function getDegreeProgrammes(array $queryParameters = [], array $options = []): iterable
    {
        return $this->getResources(
            self::API_PATH,
            DegreeProgrammeResource::class,
            $queryParameters,
            $options
        );
    }

    /**
     * @return iterable<DegreeProgrammeResource>
     */
    public function getDegreeProgrammesByDegreeProgrammeUids(array $degreeProgrammeUids, array $options = []): iterable
    {
        return $this->getDegreeProgrammes([
            self::DEGREE_PROGRAMME_UID_QUERY_PARAMETER_NAME => $degreeProgrammeUids,
        ], $options);
    }

    public function getDegreeProgrammeByUid(string $degreeProgrammeUid, array $queryParameters = []): DegreeProgrammeResource
    {
        $resource = $this->getResourceByIdentifier(
            self::API_PATH,
            DegreeProgrammeResource::class,
            $degreeProgrammeUid,
            $queryParameters
        );
        assert($resource instanceof DegreeProgrammeResource);

        return $resource;
    }

    public function getDegreeProgrammesCursorBased(
        array $queryParameters = [],
        ?string $cursor = null,
        int $maxNumItems = 30,
        array $options = []
    ): CursorBasedResourcePage {
        return $this->getResourcesCursorBased(
            self::API_PATH,
            DegreeProgrammeResource::class,
            $queryParameters,
            $cursor,
            $maxNumItems,
            $options
        );
    }
}
