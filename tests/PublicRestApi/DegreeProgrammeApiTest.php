<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Tests\PublicRestApi;

use Dbp\CampusonlineApi\PublicRestApi\Connection;
use Dbp\CampusonlineApi\PublicRestApi\Studies\DegreeProgrammeApi;
use Dbp\CampusonlineApi\PublicRestApi\Studies\DegreeProgrammeResource;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class DegreeProgrammeApiTest extends TestCase
{
    private DegreeProgrammeApi $api;

    /**
     * @var array<int, array{request: RequestInterface}>
     */
    private array $historyContainer = [];

    public const DEGREE_PROGRAMMES_RESPONSE = <<<JSON
        {
          "items": [
            {
              "uid": "675",
              "identifier": "UA 057",
              "curriculumUid": "618",
              "admissionType": "M",
              "degreeProgrammeType": "SINGLE",
              "intendedDegree": {
                "key": "00",
                "name": {
                  "value": {
                    "de": "Abschluss unbekannt",
                    "en": "Unknown certificate"
                  }
                }
              },
              "subject": {
                "uid": "310",
                "code": "057",
                "name": {
                  "value": {
                    "de": "Individuelles Diplomstudium",
                    "en": "Individual diploma programme"
                  }
                }
              },
              "partialDegreeProgrammes": [
                {
                  "uid": "676",
                  "identifier": "UA 057 001",
                  "subject": {
                    "uid": "311",
                    "code": "001",
                    "name": {
                      "value": {
                        "de": "Teilstudium Beispiel",
                        "en": "Partial degree programme example"
                      }
                    }
                  },
                  "programmeRole": {
                    "uid": "1",
                    "code": "HF",
                    "name": {
                      "value": {
                        "de": "Hauptfach",
                        "en": "Major subject"
                      }
                    },
                    "type": "MAJOR"
                  },
                  "order": 1,
                  "parentDegreeProgrammeUid": "675",
                  "curriculumUid": "619"
                }
              ]
            }
          ],
          "limit": 30,
          "nextCursor": "xxxxxxxx=="
        }
        JSON;

    public const DEGREE_PROGRAMME_RESPONSE = <<<JSON
        {
          "uid": "675",
          "identifier": "UA 057",
          "curriculumUid": "618",
          "admissionType": "M",
          "degreeProgrammeType": "SINGLE",
          "intendedDegree": {
            "key": "00",
            "name": {
              "value": {
                "de": "Abschluss unbekannt",
                "en": "Unknown certificate"
              }
            }
          },
          "subject": {
            "uid": "310",
            "code": "057",
            "name": {
              "value": {
                "de": "Individuelles Diplomstudium",
                "en": "Individual diploma programme"
              }
            }
          },
          "partialDegreeProgrammes": [
            {
              "uid": "676",
              "identifier": "UA 057 001",
              "subject": {
                "uid": "311",
                "code": "001",
                "name": {
                  "value": {
                    "de": "Teilstudium Beispiel",
                    "en": "Partial degree programme example"
                  }
                }
              },
              "programmeRole": {
                "uid": "1",
                "code": "HF",
                "name": {
                  "value": {
                    "de": "Hauptfach",
                    "en": "Major subject"
                  }
                },
                "type": "MAJOR"
              },
              "order": 1,
              "parentDegreeProgrammeUid": "675",
              "curriculumUid": "619"
            }
          ]
        }
        JSON;

    protected function setUp(): void
    {
        parent::setUp();

        $connection = new Connection('http://invalid', 'clientid', 'secret');
        $connection->setToken('nope', (new \DateTimeImmutable())->add(new \DateInterval('P1D')));

        $this->api = new DegreeProgrammeApi($connection);
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

    public function testGetDegreeProgrammesByDegreeProgrammeUids(): void
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], self::DEGREE_PROGRAMMES_RESPONSE),
        ]);

        $result = $this->api->getDegreeProgrammesByDegreeProgrammeUids(['675']);
        $resources = iterator_to_array($result);

        $this->assertCount(1, $resources);
        $this->assertRequest(
            0,
            '/co/co-sm-core/study/api/degree-programmes',
            'degree_programme_uid=675'
        );

        $degreeProgrammeResource = $resources[0];
        $this->assertDegreeProgrammeResource($degreeProgrammeResource);
    }

    public function testGetDegreeProgrammeByUid(): void
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], self::DEGREE_PROGRAMME_RESPONSE),
        ]);

        $degreeProgrammeResource = $this->api->getDegreeProgrammeByUid('675');
        $this->assertRequest(
            0,
            '/co/co-sm-core/study/api/degree-programmes/675'
        );

        $this->assertDegreeProgrammeResource($degreeProgrammeResource);
    }

    public function testGetDegreeProgrammesCursorBased(): void
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], self::DEGREE_PROGRAMMES_RESPONSE),
        ]);

        $result = $this->api->getDegreeProgrammesCursorBased();
        $resources = iterator_to_array($result->getResources());
        $this->assertRequest(
            0,
            '/co/co-sm-core/study/api/degree-programmes',
            'limit=30'
        );

        $this->assertCount(1, $resources);
        $this->assertSame('xxxxxxxx==', $result->getNextCursor());

        $degreeProgrammeResource = $resources[0];
        assert($degreeProgrammeResource instanceof DegreeProgrammeResource);

        $this->assertDegreeProgrammeResource($degreeProgrammeResource);
    }

    private function assertRequest(int $requestIndex, string $expectedPath, string $expectedQuery = ''): void
    {
        $this->assertArrayHasKey($requestIndex, $this->historyContainer);

        $request = $this->historyContainer[$requestIndex]['request'];

        $this->assertSame($expectedPath, $request->getUri()->getPath());
        $this->assertSame($expectedQuery, $request->getUri()->getQuery());
    }

    private function assertDegreeProgrammeResource(DegreeProgrammeResource $degreeProgrammeResource): void
    {
        $this->assertSame('675', $degreeProgrammeResource->getUid());
        $this->assertSame('UA 057', $degreeProgrammeResource->getIdentifier());
        $this->assertSame('618', $degreeProgrammeResource->getCurriculumUid());
        $this->assertSame('M', $degreeProgrammeResource->getAdmissionType());
        $this->assertSame('SINGLE', $degreeProgrammeResource->getDegreeProgrammeType());
        $this->assertSame('00', $degreeProgrammeResource->getIntendedDegreeKey());
        $this->assertSame([
            'de' => 'Abschluss unbekannt',
            'en' => 'Unknown certificate',
        ], $degreeProgrammeResource->getIntendedDegreeName());
        $this->assertSame('310', $degreeProgrammeResource->getSubjectUid());
        $this->assertSame('057', $degreeProgrammeResource->getSubjectCode());
        $this->assertSame([
            'de' => 'Individuelles Diplomstudium',
            'en' => 'Individual diploma programme',
        ], $degreeProgrammeResource->getSubjectName());

        $partialDegreeProgrammes = $degreeProgrammeResource->getPartialDegreeProgrammes();

        $this->assertCount(1, $partialDegreeProgrammes);

        $partialDegreeProgrammeResource = $partialDegreeProgrammes[0];

        $this->assertSame('676', $partialDegreeProgrammeResource->getUid());
        $this->assertSame('UA 057 001', $partialDegreeProgrammeResource->getIdentifier());
        $this->assertSame('311', $partialDegreeProgrammeResource->getSubjectUid());
        $this->assertSame('001', $partialDegreeProgrammeResource->getSubjectCode());
        $this->assertSame([
            'de' => 'Teilstudium Beispiel',
            'en' => 'Partial degree programme example',
        ], $partialDegreeProgrammeResource->getSubjectName());
        $this->assertSame('1', $partialDegreeProgrammeResource->getProgrammeRoleUid());
        $this->assertSame('HF', $partialDegreeProgrammeResource->getProgrammeRoleCode());
        $this->assertSame([
            'de' => 'Hauptfach',
            'en' => 'Major subject',
        ], $partialDegreeProgrammeResource->getProgrammeRoleName());
        $this->assertSame('MAJOR', $partialDegreeProgrammeResource->getProgrammeRoleType());
        $this->assertSame(1, $partialDegreeProgrammeResource->getOrder());
        $this->assertSame('675', $partialDegreeProgrammeResource->getParentDegreeProgrammeUid());
        $this->assertSame('619', $partialDegreeProgrammeResource->getCurriculumUid());
    }
}
