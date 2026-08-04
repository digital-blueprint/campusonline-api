<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Tests\Helpers;

use Dbp\CampusonlineApi\Helpers\ApiException;
use Dbp\CampusonlineApi\Helpers\GuzzleTools;
use Dbp\CampusonlineApi\Rest\Tools;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;

class GuzzleToolsTest extends TestCase
{
    public function testGetResponseFromExceptionWithoutResponse(): void
    {
        $request = new Request('GET', 'http://localhost');

        $this->assertNull(GuzzleTools::getResponseFromException(
            new RequestException('nope', $request)));
        $this->assertNull(GuzzleTools::getResponseFromException(
            new ConnectException('nope', $request)));
        $this->assertNull(GuzzleTools::getResponseFromException(
            new \RuntimeException('nope')));
    }

    public function testGetResponseFromExceptionWithResponse(): void
    {
        $request = new Request('GET', 'http://localhost');
        $response = new Response(404);
        $exception = RequestException::create($request, $response);

        $this->assertSame($response, GuzzleTools::getResponseFromException($exception));
    }

    public function testApiExceptionWithoutResponse(): void
    {
        $exception = ApiException::fromGuzzleException(
            new RequestException('nope', new Request('GET', 'http://localhost')));
        $this->assertFalse($exception->isHttpResponseCode());
    }

    public function testApiExceptionWithResponse(): void
    {
        $exception = ApiException::fromGuzzleException(RequestException::create(
            new Request('GET', 'http://localhost'), new Response(404)));
        $this->assertTrue($exception->isHttpResponseCode());
        $this->assertTrue($exception->isHttpResponseCodeNotFound());
    }

    public function testCreateApiExceptionFromJsonResponseWithoutResponse(): void
    {
        $exception = Tools::createApiExceptionFromJsonResponse(
            new RequestException('nope', new Request('GET', 'http://localhost')));
        $this->assertFalse($exception->isHttpResponseCode());
    }

    public function testCreateApiExceptionFromJsonResponse(): void
    {
        $body = json_encode([
            'type' => 'resources',
            'resource' => [
                [
                    'content' => [
                        'coErrorDto' => [
                            'errorType' => 'SOME_ERROR',
                            'httpCode' => '404',
                            'message' => 'Not there',
                        ],
                    ],
                ],
            ],
        ]);

        $response = new Response(404, [], Utils::streamFor($body));
        // simulate a body which was already read, for example by the body summarizer
        $response->getBody()->getContents();

        $exception = Tools::createApiExceptionFromJsonResponse(RequestException::create(
            new Request('GET', 'http://localhost'), $response));

        $this->assertSame('SOME_ERROR[404]: Not there', $exception->getMessage());
        $this->assertSame(404, $exception->getCode());
        $this->assertTrue($exception->isHttpResponseCodeNotFound());
    }
}
