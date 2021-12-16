<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Rest\UCard;

class UCard
{
    public $ident;
    public $contentId;
    public $contentSize;
    public $isUpdatable;
    public $cardType;

    public function __construct(string $ident, string $cardType, string $contentId, int $contentSize, bool $isUpdatable)
    {
        $this->ident = $ident;
        $this->contentId = $contentId;
        $this->contentSize = $contentSize;
        $this->isUpdatable = $isUpdatable;
        $this->cardType = $cardType;
    }
}
