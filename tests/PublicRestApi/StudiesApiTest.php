<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Tests\PublicRestApi;

use Dbp\CampusonlineApi\PublicRestApi\Connection;
use Dbp\CampusonlineApi\PublicRestApi\Studies\StudiesApi;
use Dbp\CampusonlineApi\PublicRestApi\Studies\StudiesResource;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class StudiesApiTest extends TestCase
{
    private StudiesApi $api;
    /**
     * @var array<int, array{request: \Psr\Http\Message\RequestInterface}>
     */
    private array $historyContainer = [];
    public const STUDIES_RESPONSE = <<<JSON
        {
          "items": [
            {
              "uid": "195685",
              "degreeProgrammeUid": "675",
              "personUid": "B0A1EB6E192CB00E",
              "curriculumVersionUid": "618",
              "registrationStatusType": "Z",
              "partialStudies": [
                {
                  "uid": "195686",
                  "partialDegreeProgrammeUid": "676",
                  "curriculumVersionUid": "619",
                  "registrationStatusType": "I"
                }
              ]
            }
          ]
        }
        JSON;

    public const STUDY_RESPONSE = <<<JSON
        {
          "uid": "195685",
          "degreeProgrammeUid": "675",
          "personUid": "B0A1EB6E192CB00E",
          "curriculumVersionUid": "618",
          "registrationStatusType": "Z",
          "partialStudies": [
            {
              "uid": "195686",
              "partialDegreeProgrammeUid": "676",
              "curriculumVersionUid": "619",
              "registrationStatusType": "I"
            }
          ]
        }
        JSON;

    protected function setUp(): void
    {
        parent::setUp();

        $connection = new Connection('http://invalid', 'clientid', 'secret');
        $connection->setToken('nope', (new \DateTimeImmutable())->add(new \DateInterval('P1D')));

        $this->api = new StudiesApi($connection);
        $this->mockResponses([]);
    }

    private function mockResponses(array $responses): void
    {
        $this->historyContainer = [];
        $historyContainer = &$this->historyContainer;

        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($historyContainer));

        $this->api->setClientHandler($stack);
    }

    public function testGetStudiesByPersonUids(): void
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], self::STUDIES_RESPONSE),
        ]);

        $result = $this->api->getStudiesByPersonUids(['B0A1EB6E192CB00E']);
        $resources = iterator_to_array($result);

        $this->assertRequest(
            0,
            '/co/co-sm-core/study/api/studies',
            'person_uid=B0A1EB6E192CB00E'
        );
        $this->assertCount(1, $resources);

        $studiesResource = $resources[0];
        $this->assertStudiesResource($studiesResource);
    }

    public function testGetStudiesByStudyUids(): void
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], self::STUDIES_RESPONSE),
        ]);

        $result = $this->api->getStudiesByStudyUids(['195685']);
        $resources = iterator_to_array($result);

        $this->assertCount(1, $resources);

        $studiesResource = $resources[0];

        $this->assertStudiesResource($studiesResource);
    }

    public function testGetStudyByUid(): void
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], self::STUDY_RESPONSE),
        ]);

        $studiesResource = $this->api->getStudyByUid('195685');
        $this->assertRequest(
            0,
            '/co/co-sm-core/study/api/studies/195685'
        );

        $this->assertStudiesResource($studiesResource);
    }

    private function assertStudiesResource(StudiesResource $studiesResource): void
    {
        $this->assertSame('195685', $studiesResource->getUid());
        $this->assertSame('675', $studiesResource->getDegreeProgrammeUid());
        $this->assertSame('B0A1EB6E192CB00E', $studiesResource->getPersonUid());
        $this->assertSame('618', $studiesResource->getCurriculumVersionUid());
        $this->assertSame('Z', $studiesResource->getRegistrationStatusType());

        $partialStudies = $studiesResource->getPartialStudies();

        $this->assertCount(1, $partialStudies);

        $partialStudyResource = $partialStudies[0];

        $this->assertSame('195686', $partialStudyResource->getUid());
        $this->assertSame('676', $partialStudyResource->getPartialDegreeProgrammeUid());
        $this->assertSame('619', $partialStudyResource->getCurriculumVersionUid());
        $this->assertSame('I', $partialStudyResource->getRegistrationStatusType());
    }

    private function assertRequest(int $requestIndex, string $expectedPath, string $expectedQuery = ''): void
    {
        $this->assertArrayHasKey($requestIndex, $this->historyContainer);

        $request = $this->historyContainer[$requestIndex]['request'];

        $this->assertSame($expectedPath, $request->getUri()->getPath());
        $this->assertSame($expectedQuery, $request->getUri()->getQuery());
    }
}
