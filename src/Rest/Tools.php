<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Rest;

use Dbp\CampusonlineApi\Helpers\ApiException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
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
        if (strlen($input) === 0) {
            throw new \ValueError('Column name can\'t be empty');
        }

        // XXX: Unclear what is really allowed
        if (preg_match('/[^A-Z0-9_]/', $input)) {
            throw new \ValueError('Column name contains invalid characters');
        }

        return $input;
    }

    /**
     * @param string[] $input
     */
    public static function validateFilterValueList(array $input): string
    {
        if (count($input) === 0) {
            throw new \ValueError('Value list can\'t be empty');
        }

        foreach ($input as $item) {
            self::validateFilterValue($item);
            if (str_contains($item, ',')) {
                throw new \ValueError('filter value not allowed to contain ","');
            }
        }

        return implode(',', $input);
    }

    public static function validateFilterValue(string $input): string
    {
        // Filtering breaks if the value is empty, so don't allow
        if (strlen($input) === 0) {
            throw new \ValueError('empty filter value not allowed');
        }

        // XXX: Unclear what is really allowed
        if (preg_match('/[ !#$&\'*+\/:;=?@\[\]"><>\\\^`{}|~£+]/', $input)) {
            throw new \ValueError('Contains invalid characters');
        }

        return $input;
    }

    /**
     * Like json_decode but throws on invalid json data.
     *
     * @throws \JsonException
     */
    public static function decodeJSON(string $json, bool $assoc = false)
    {
        return json_decode($json, $assoc, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @deprecated Since v0.3.10. Use Tools::createApiExceptionFromJsonResponse instead
     */
    public static function createResponseError(RequestException $e): ApiException
    {
        return self::createApiExceptionFromJsonResponse($e);
    }

    public static function createApiExceptionFromJsonResponse(GuzzleException $guzzleException): ApiException
    {
        $response = null;
        if ($guzzleException instanceof RequestException) {
            $response = $guzzleException->getResponse();
            if ($response !== null) {
                $data = (string) $response->getBody();
                $json = [];
                try {
                    $json = Tools::decodeJSON($data, true);
                } catch (\Exception $exception) {
                }

                if (($json['type'] ?? null) === 'resources') {
                    $coErrorDto = $json['resource'][0]['content']['coErrorDto'];
                    $message = $coErrorDto['errorType'].'['.$coErrorDto['httpCode'].']: '.$coErrorDto['message'];

                    return new ApiException($message, intval($coErrorDto['httpCode']), true);
                }
            }
        }

        return new ApiException($guzzleException->getMessage(), $guzzleException->getCode(), $response !== null);
    }
}
