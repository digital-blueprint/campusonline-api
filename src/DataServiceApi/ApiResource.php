<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\DataServiceApi;

/**
 * A generic API resource.
 */
readonly class ApiResource
{
    /**
     * @param string $type    The type of the resource
     * @param array  $content The content of the resource
     */
    public function __construct(public string $type, public array $content)
    {
    }
}
