<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\DataServiceApi;

use Dbp\CampusonlineApi\Helpers\ApiException;

readonly class PuQueryDataServiceApi extends AbstractDataServiceApi
{
    /**
     * @throws ApiException
     */
    public function getResource(string $field, string $value): ?ApiResource
    {
        $collection = $this->getResourceCollection([$field => $value]);
        if (count($collection) === 1) {
            return $collection[0];
        } elseif (count($collection) > 1) {
            throw new ApiException("Filter on '$field' with '$value' returned multiple results while only <=1 is allowed");
        }

        return null;
    }

    /**
     * @return ApiResource[]
     *
     * @throws ApiException
     */
    public function getResourceCollection(array $filters = []): array
    {
        return $this->get('{?%24format}{&params*}',
            [
                '%24format' => 'json',
                'params' => $filters,
            ]);
    }
}
