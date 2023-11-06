<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Rest\UCard;

/**
 * @deprecated Use Dbp\CampusonlineApi\Rest\Generic\ApiResource instead
 */
class UCardPicture
{
    public $id;
    public $content;

    public function __construct(string $id, string $content)
    {
        $this->id = $id;
        $this->content = $content;
    }
}
