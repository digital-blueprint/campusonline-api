<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Helpers;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 */
class GuzzleTools
{
    /**
     * Returns the response attached to the given exception, or null if there is none.
     *
     * Works with both Guzzle 7, where the response lives on RequestException, and Guzzle 8,
     * where it was moved to ResponseException (a subclass of RequestException).
     */
    public static function getResponseFromException(\Throwable $exception): ?ResponseInterface
    {
        if ($exception instanceof RequestException && method_exists($exception, 'getResponse')) {
            return $exception->getResponse();
        }

        return null;
    }
}
