<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Persons;

use Dbp\CampusonlineApi\Helpers\ApiException;
use Dbp\CampusonlineApi\PublicRestApi\AbstractApi;
use Dbp\CampusonlineApi\PublicRestApi\CursorBasedResourcePage;

class PersonClaimsApi extends AbstractApi
{
    public const CLAIM_QUERY_PARAMETER_NAME = 'claim';
    public const EMAIL_QUERY_PARAMETER_NAME = 'email';
    public const GIVEN_NAME_LIKE_QUERY_PARAMETER_NAME = 'given_name_like';
    public const SURNAME_LIKE_QUERY_PARAMETER_NAME = 'surname_like';
    public const MATRICULATION_NUMBER_QUERY_PARAMETER_NAME = 'matriculation_number';
    public const PERSON_GROUP_KEY_QUERY_PARAMETER_NAME = 'person_group_key';
    public const PERSON_UID_QUERY_PARAMETER_NAME = 'person_uid';
    public const SORT_QUERY_PARAMETER_NAME = 'sort';

    public const EMPLOYEE_PERSON_GROUP_KEY = 'EMPLOYEE';
    public const STUDENT_PERSON_GROUP_KEY = 'STUDENT';
    public const EXTERNAL_PERSON_GROUP_KEY = 'EXTPERS';

    public const ALL_CLAIM = 'CO_CLAIM_ALL';
    public const NAME_CLAIM = 'CO_CLAIM_NAME';
    public const MATRICULATION_NUMBER_CLAIM = 'CO_CLAIM_MATRICULATION_NUMBER';
    public const EMAIL_CLAIM = 'CO_CLAIM_EMAIL';
    public const TITLE_CLAIM = 'CO_CLAIM_TITLE';

    private const DEFAULT_CLAIMS = [
        self::NAME_CLAIM,
    ];

    private const API_PATH = Common::API_PATH.'/person-claims';

    public function getPersonClaimsByPersonUid(string $personUid, array $claims = self::DEFAULT_CLAIMS, array $options = []): PersonClaimsResource
    {
        $personClaimsPage = iterator_to_array($this->getResources(
            self::API_PATH,
            PersonClaimsResource::class,
            [
                self::PERSON_UID_QUERY_PARAMETER_NAME => $personUid,
                self::CLAIM_QUERY_PARAMETER_NAME => $claims,
            ]));

        if (($personClaims = $personClaimsPage[0] ?? null) === null) {
            throw new ApiException('person not found', ApiException::HTTP_NOT_FOUND, true);
        }

        return $personClaims;
    }

    public function getPersonClaimsPageCursorBased(array $queryParameters = [],
        array $claims = self::DEFAULT_CLAIMS,
        ?string $cursor = null, int $maxNumItems = 30, array $options = []): CursorBasedResourcePage
    {
        $queryParameters[self::CLAIM_QUERY_PARAMETER_NAME] = $claims;

        return $this->getResourcesCursorBased(
            self::API_PATH, PersonClaimsResource::class,
            $queryParameters, $cursor, $maxNumItems);
    }

    public function getPersonClaimsPageOffsetBased(array $queryParameters = [],
        array $claims = self::DEFAULT_CLAIMS,
        int $firstItemIndex = 0, int $maxNumItems = 30, array $options = []): iterable
    {
        $queryParameters[self::CLAIM_QUERY_PARAMETER_NAME] = $claims;

        return $this->getResourcesOffsetBased(
            self::API_PATH, PersonClaimsResource::class,
            $queryParameters, $firstItemIndex, $maxNumItems);
    }
}
