<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Studies;

use Dbp\CampusonlineApi\PublicRestApi\AbstractApi;

class StudiesApi extends AbstractApi
{
    public const PERSON_UID_QUERY_PARAMETER_NAME = 'person_uid';
    public const STUDY_UID_QUERY_PARAMETER_NAME = 'study_uid';
    private const API_PATH = Common::API_PATH.'/studies';

    /**
     * @return iterable<StudiesResource>
     */
    public function getStudies(array $queryParameters = [], array $options = []): iterable
    {
        return $this->getResources(
            self::API_PATH,
            StudiesResource::class,
            $queryParameters,
            $options
        );
    }

    /**
     * @return iterable<StudiesResource>
     */
    public function getStudiesByPersonUids(array $personUids, array $options = []): iterable
    {
        return $this->getStudies([
            self::PERSON_UID_QUERY_PARAMETER_NAME => $personUids,
        ], $options);
    }

    /**
     * @return iterable<StudiesResource>
     */
    public function getStudiesByStudyUids(array $studyUids, array $options = []): iterable
    {
        return $this->getStudies([
            self::STUDY_UID_QUERY_PARAMETER_NAME => $studyUids,
        ], $options);
    }

    public function getStudyByUid(string $studyUid, array $queryParameters = []): StudiesResource
    {
        $resource = $this->getResourceByIdentifier(
            self::API_PATH,
            StudiesResource::class,
            $studyUid,
            $queryParameters
        );
        assert($resource instanceof StudiesResource);

        return $resource;
    }
}
