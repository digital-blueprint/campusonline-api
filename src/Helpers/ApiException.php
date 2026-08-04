<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Helpers;

use GuzzleHttp\Exception\GuzzleException;

class ApiException extends \RuntimeException
{
    public const HTTP_NOT_FOUND = 404;
    public const HTTP_UNAUTHORIZED = 401;

    public static function fromGuzzleException(GuzzleException $guzzleException): ApiException
    {
        return new ApiException($guzzleException->getMessage(), $guzzleException->getCode(),
            GuzzleTools::getResponseFromException($guzzleException) !== null);
    }

    public function __construct(string $message = '', int $code = 0,
        private readonly bool $isHttpResponseCode = false, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
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
