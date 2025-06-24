<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi;

use Dbp\CampusonlineApi\Rest\Tools;

class TestApi extends Api
{
    public function getApis(): array
    {
        return Tools::decodeJsonResponse($this->connection->getClient()->get('co/public/api/apis'));
    }
}
