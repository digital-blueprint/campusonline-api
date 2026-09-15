<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Persons;

use Dbp\CampusonlineApi\PublicRestApi\AbstractApi;

class PersonIdentifiersApi extends AbstractApi
{
    public const CO_CLAIM_PERSON_UID_CLAIM = 'CO_CLAIM_PERSON_UID';
    public const CO_CLAIM_MATRICULATION_NUMBER_CLAIM = 'CO_CLAIM_MATRICULATION_NUMBER';
    public const CO_CLAIM_PERSON_INTERNAL_ID_CLAIM = 'CO_CLAIM_PERSON_INTERNAL_ID';
    public const CO_CLAIM_EXT_IDENT_ID_CLAIM = 'CO_CLAIM_EXT_IDENT_ID';
    public const CO_CLAIM_STUDENT_INTERNAL_ID_CLAIM = 'CO_CLAIM_STUDENT_INTERNAL_ID';
    public const CO_CLAIM_EMPLOYEE_INTERNAL_ID_CLAIM = 'CO_CLAIM_EMPLOYEE_INTERNAL_ID';
    public const CO_CLAIM_EXTPERS_INTERNAL_ID_CLAIM = 'CO_CLAIM_EXTPERS_INTERNAL_ID';
    public const CO_CLAIM_EXTERNAL_SYSTEM_UID_CLAIM = 'CO_CLAIM_EXTERNAL_SYSTEM_UID';
    public const CO_CLAIM_USERNAME_CLAIM = 'CO_CLAIM_USERNAME';
    public const CO_CLAIM_EMAIL_ALL_CLAIM = 'CO_CLAIM_EMAIL_ALL';

    private const UID_QUERY_PARAMETER_NAME = 'uid';

    private const SOURCE_CLAIM_QUERY_PARAMETER_NAME = 'source_claim';
    private const TARGET_CLAIM_QUERY_PARAMETER_NAME = 'target_claim';

    private const API_PATH = Common::API_PATH.'/person-identifiers';
    private const MAPPINGS_IDENTIFIER = 'mappings';

    public function mapPersonIdentifier(string $uid,
        string $sourceClaim, string $targetClaim, array $options = []): PersonIdentifiersMappingResource
    {
        $resource = $this->getResourceByIdentifier(
            self::API_PATH, PersonIdentifiersMappingResource::class,
            self::MAPPINGS_IDENTIFIER, [
                self::UID_QUERY_PARAMETER_NAME => $uid,
                self::SOURCE_CLAIM_QUERY_PARAMETER_NAME => $sourceClaim,
                self::TARGET_CLAIM_QUERY_PARAMETER_NAME => $targetClaim,
            ]);
        assert($resource instanceof PersonIdentifiersMappingResource);

        return $resource;
    }
}
