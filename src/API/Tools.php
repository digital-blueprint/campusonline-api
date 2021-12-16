<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\API;

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

    public static function validateFilterValue(string $input): string
    {
        // Filtering breaks if the value is empty, so don't allow
        if (strlen($input) === 0) {
            throw new \ValueError('empty value not allowed');
        }

        // XXX: Filter expressions are delimited by ";" and I don't know how
        // to escape them, which could lead to filter injections, so throw.
        if (\str_contains($input, ';')) {
            throw new \ValueError('value not allowed to contain ";"');
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

    public static function createResponseError(RequestException $e): APIException
    {
        $response = $e->getResponse();
        if ($response === null) {
            return new APIException('Unknown error');
        }
        $data = (string) $response->getBody();
        $json = Tools::decodeJSON($data, true);
        if (($json['type'] ?? '') === 'resources') {
            $coErrorDto = $json['resource'][0]['content']['coErrorDto'];
            $message = $coErrorDto['errorType'].'['.$coErrorDto['httpCode'].']: '.$coErrorDto['message'];

            return new APIException($message);
        } else {
            return new APIException($json['type']);
        }
    }
}
