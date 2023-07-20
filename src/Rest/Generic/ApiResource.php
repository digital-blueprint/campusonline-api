<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Rest\Generic;

/**
 * A generic API resource.
 */
class ApiResource
{
    /**
     * The type of the resource.
     *
     * @var string
     */
    public $type;

    /**
     * The content of the resources.
     * This is just the returned content JSON decoded.
     *
     * @var array
     */
    public $content;
}
