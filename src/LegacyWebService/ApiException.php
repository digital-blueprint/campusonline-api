<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\LegacyWebService;

class ApiException extends \Exception
{
    private const HTTP_NOT_FOUND = 404;
    private const HTTP_UNAUTHORIZED = 401;

    private $isHttpResponseCode;

    public function __construct(string $message = '', int $code = 0,
                                bool $isHttpResponseCode = false, \Throwable $previous = null)
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
