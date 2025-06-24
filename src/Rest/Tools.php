<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Rest;

use Dbp\CampusonlineApi\Helpers\ApiException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Psr\Http\Message\ResponseInterface;
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
        // Strictly not needed here, but gives a better error message for this common issue
        if ($input === '') {
            throw new \ValueError('empty filter value not allowed');
        }

        $parts = self::extractValidFilterSubstrings($input);
        if (count($parts) !== 1 || $parts[0] !== $input) {
            throw new \ValueError('Contains invalid characters');
        }

        return $input;
    }

    /**
     * @return string[]
     */
    public static function extractValidFilterSubstrings(string $value): array
    {
        // XXX: Unclear what is really allowed
        return preg_split('/[^\p{L}\p{N}._,-]+/u', $value, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Like json_decode but throws on invalid json data.
     *
     * @throws \JsonException
     */
    public static function decodeJSON(string $json, bool $assoc = false): mixed
    {
        $data = null;
        try {
            $data = json_decode($json, $assoc, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $jsonException) {
            $throw = true;
            if (in_array($jsonException->getCode(), [JSON_ERROR_CTRL_CHAR, JSON_ERROR_SYNTAX], true)) {
                // escape illegal control characters from JSON string
                $json = preg_replace_callback('/[\x00-\x1F\x7F]/',
                    function ($matches) {
                        return sprintf('\\u%04x', ord($matches[0]));
                    }, $json);
                try {
                    $data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
                    $throw = false;
                } catch (\JsonException) {
                }
            }
            if ($throw) {
                throw new ApiException('API response is not valid JSON');
            }
        }

        return $data;
    }

    /**
     * @throws ApiException
     */
    public static function decodeJsonResponse(ResponseInterface $response): mixed
    {
        try {
            return Tools::decodeJSON($response->getBody()->getContents(), true);
        } catch (\JsonException $exception) {
            throw new ApiException('failed to decode JSON response');
        }
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
