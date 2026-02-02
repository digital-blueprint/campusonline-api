<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\Persons;

use Dbp\CampusonlineApi\PublicRestApi\Resource;

class PersonIdentifiersMappingResource extends Resource
{
    private const SOURCE_TYPE_ATTRIBUTE = 'source_type';
    private const TARGET_TYPE_ATTRIBUTE = 'target_type';
    private const MAPPINGS_ATTRIBUTE = 'mappings';

    public function getSourceType(): ?string
    {
        return $this->resourceData[self::SOURCE_TYPE_ATTRIBUTE] ?? null;
    }

    public function getTargetType(): ?string
    {
        return $this->resourceData[self::TARGET_TYPE_ATTRIBUTE] ?? null;
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    public function getMappings(): ?array
    {
        return $this->resourceData[self::MAPPINGS_ATTRIBUTE] ?? null;
    }
}
