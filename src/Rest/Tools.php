<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Rest;

use Dbp\CampusonlineApi\Helpers\ApiException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Psr\Log\LoggerInterface;

class Tools
{
    public static function createLoggerMiddleware(LoggerInterface $logger): callable
    {
        return Middleware::log(
            $logger,
            new MessageFormatter('[{method}] {uri}: CODE={code}, ERROR={error}, CACHE={res_header_X-Kevinrob-Cache}')
        );
    }

    /**
     * Throws in case a value is not suitable as a filter field name.
     *
     * @throws \ValueError
     */
    public static function validateFilterName(string $input): string
    {
        // Filter names are separated by '-' to the operator
        if (str_contains($input, '-')) {
            throw new \ValueError('filter name not allowed to contain "-"');
        }

        // XXX: Filter names are delimited by ";" and I don't know how
        // to escape them, which could lead to filter injections, so throw.
        if (str_contains($input, ';')) {
            throw new \ValueError('filter name not allowed to contain ";"');
        }

        return $input;
    }

    /**
     * @param mixed $input
     *
     * @return mixed
     *
     * @throws \ValueError
     */
    public static function validateFilterValue($input)
    {
        if (is_string($input)) {
            // Filtering breaks if the value is empty, so don't allow
            if (strlen($input) === 0) {
                throw new \ValueError('empty filter value not allowed');
            }

            // Filtering breaks if the value contains a whitespace, so don't allow
            if (str_contains($input, ' ')) {
                throw new \ValueError('filter value not allowed to contain whitespaces');
            }

            // XXX: Filter expressions are delimited by ";" and I don't know how
            // to escape them, which could lead to filter injections, so throw.
            if (str_contains($input, ';')) {
                throw new \ValueError('filter value not allowed to contain ";"');
            }
        }

        return $input;
    }

    /**
     * Like json_decode but throws on invalid json data.
     *
     * @return mixed
     *
     * @throws \JsonException
     */
    public static function decodeJSON(string $json, bool $assoc = false)
    {
        return json_decode($json, $assoc, 512, JSON_THROW_ON_ERROR);
    }

    public static function createResponseError(RequestException $e): ApiException
    {
        $response = $e->getResponse();
        if ($response === null) {
            return new ApiException('Unknown error');
        }
        $data = (string) $response->getBody();
        $json = [];
        try {
            $json = Tools::decodeJSON($data, true);
        } catch (\JsonException $exception) {
        }

        if (($json['type'] ?? '') === 'resources') {
            $coErrorDto = $json['resource'][0]['content']['coErrorDto'];
            $message = $coErrorDto['errorType'].'['.$coErrorDto['httpCode'].']: '.$coErrorDto['message'];

            return new ApiException($message, intval($coErrorDto['httpCode']), true);
        } else {
            return new ApiException($json['type']);
        }
    }
}
