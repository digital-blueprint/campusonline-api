<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\UCard;

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
