<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Rest\UCard;

/**
 * @deprecated Use Dbp\CampusonlineApi\Rest\Generic\ApiResource instead
 */
class UCard
{
    public $identIdObfuscated;
    public $contentId;
    public $contentSize;
    public $isUpdatable;
    public $cardType;

    public function __construct(string $identIdObfuscated, string $cardType, string $contentId, int $contentSize, bool $isUpdatable)
    {
        $this->identIdObfuscated = $identIdObfuscated;
        $this->contentId = $contentId;
        $this->contentSize = $contentSize;
        $this->isUpdatable = $isUpdatable;
        $this->cardType = $cardType;
    }
}
