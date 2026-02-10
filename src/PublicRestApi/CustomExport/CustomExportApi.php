<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi\CustomExport;

use Dbp\CampusonlineApi\PublicRestApi\AbstractApi;
use Dbp\CampusonlineApi\PublicRestApi\Connection;

class CustomExportApi extends AbstractApi
{
    public function __construct(Connection $connection,
        private readonly string $customExportApiPath)
    {
        parent::__construct($connection);
    }

    public function getCustomResources(array $queryParameters = []): iterable
    {
        return $this->getResources($this->customExportApiPath, CustomResource::class, $queryParameters);
    }
}
