<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Persons;

use Dbp\CampusonlineApi\PublicRestApi\AbstractApi;

class PersonIdentifiersApi extends AbstractApi
{
    public const SOURCE_CLAIM_QUERY_PARAMETER_NAME = 'source_claim';
    public const TARGET_CLAIM_QUERY_PARAMETER_NAME = 'target_claim';

    private const API_PATH = Common::API_PATH.'/person-identifiers';
    private const MAPPINGS_IDENTIFIER = 'mappings';

    public function mapPersonIdentifier(string $sourceIdentifier,
        string $sourceClaim, string $targetClaim, array $options = []): PersonIdentifiersMappingResource
    {
        $resource = $this->getResourceByIdentifier(
            self::API_PATH, PersonIdentifiersMappingResource::class,
            self::MAPPINGS_IDENTIFIER, [
                self::SOURCE_CLAIM_QUERY_PARAMETER_NAME => $sourceClaim,
                self::TARGET_CLAIM_QUERY_PARAMETER_NAME => $targetClaim,
            ]);
        assert($resource instanceof PersonIdentifiersMappingResource);

        return $resource;
    }
}
