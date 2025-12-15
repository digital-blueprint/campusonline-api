<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi;

use Dbp\CampusonlineApi\Rest\Tools;

class GetApisApi extends AbstractApi
{
    public function getApis(): array
    {
        return Tools::decodeJsonResponse($this->getClient()->get('co/public/api/apis'));
    }
}
