<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\PublicRestApi;

readonly class CursorBasedResourcePage
{
    private const RESPONSE_DATA_ITEMS_ATTRIBUTE = 'items';
    private const RESPONSE_DATA_NEXT_CURSOR_ATTRIBUTE = 'nextCursor';

    public static function createEmptyPage(?string $cursor): self
    {
        return new CursorBasedResourcePage(new \EmptyIterator(), $cursor);
    }

    public static function createFromResponseData(array $responseData, string $className): self
    {
        return new self(
            self::getResourceGenerator($responseData, $className),
            $responseData[self::RESPONSE_DATA_NEXT_CURSOR_ATTRIBUTE] ?? null);
    }

    private static function getResourceGenerator(array $responseData, string $className): \Generator
    {
        foreach ($responseData[self::RESPONSE_DATA_ITEMS_ATTRIBUTE] ?? [] as $item) {
            yield new $className($item);
        }
    }

    public function __construct(
        private \Iterator $resources,
        private ?string $nextCursor)
    {
    }

    public function getResources(): iterable
    {
        return $this->resources;
    }

    public function getNextCursor(): ?string
    {
        return $this->nextCursor;
    }
}
