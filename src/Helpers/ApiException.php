<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Helpers;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

class ApiException extends \RuntimeException
{
    private const HTTP_NOT_FOUND = 404;
    private const HTTP_UNAUTHORIZED = 401;

    private $isHttpResponseCode;

    public static function fromGuzzleException(GuzzleException $guzzleException): ApiException
    {
        return new ApiException($guzzleException->getMessage(), $guzzleException->getCode(),
            $guzzleException instanceof RequestException && $guzzleException->getResponse() !== null);
    }

    public function __construct(string $message = '', int $code = 0,
        bool $isHttpResponseCode = false, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->isHttpResponseCode = $isHttpResponseCode;
    }

    public function isHttpResponseCode(): bool
    {
        return $this->isHttpResponseCode;
    }

    public function isHttpResponseCodeNotFound(): bool
    {
        return $this->isHttpResponseCode && $this->getCode() === self::HTTP_NOT_FOUND;
    }

    public function isHttpResponseCodeUnauthorized(): bool
    {
        return $this->isHttpResponseCode && $this->getCode() === self::HTTP_UNAUTHORIZED;
    }
}
