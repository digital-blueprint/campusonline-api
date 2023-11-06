<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Rest\Student;

/**
 * @deprecated Use Dbp\CampusonlineApi\Rest\Generic\ApiResource instead
 */
class StudentData
{
    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * @var string
     */
    public $identId;

    /**
     * @var string
     */
    public $identIdObfuscated;

    /**
     * @var string
     */
    public $personId;
}
