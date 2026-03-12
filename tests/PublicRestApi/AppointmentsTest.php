<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Tests\PublicRestApi;

use Dbp\CampusonlineApi\PublicRestApi\Appointments\AppointmentApi;
use Dbp\CampusonlineApi\PublicRestApi\Appointments\AppointmentResource;
use Dbp\CampusonlineApi\PublicRestApi\Connection;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class AppointmentsTest extends TestCase
{
    private $api;

    public const RESPONSE = '{
  "items": [
    {
      "uid": "1234",
      "applicationTypeKey": "LEH",
      "courseGroupUid": "12345",
      "courseUid": "23456",
      "endAt": "2000-10-06T09:45:00",
      "eventTypeKey": "REGULAR",
      "externalObjectUid": "56789",
      "resourceUId": "987",
      "resourceUrl": "https://example.com/ee/rest/pages/slc.cp.apt/resource/3568",
      "roomUid": "3568",
      "startAt": "2000-10-06T08:15:00",
      "statusTypeKey": "CONFIRMED"
    },
    {
      "uid": "2345",
      "applicationTypeKey": "LEH",
      "courseGroupUid": "45627",
      "courseUid": "12356",
      "endAt": "2000-10-13T09:45:00",
      "eventTypeKey": "REGULAR",
      "externalObjectUid": "98765",
      "resourceUId": "181",
      "resourceUrl": "https://example.com/ee/rest/pages/slc.cp.apt/resource/4242",
      "roomUid": "4242",
      "startAt": "2000-10-13T08:15:00",
      "statusTypeKey": "CONFIRMED"
    }
  ],
  "limit": 2,
  "nextCursor": "xxxxxxxx=="
}';

    protected function setUp(): void
    {
        parent::setUp();
        $connection = new Connection('http://invalid', 'clientid', 'secret');
        $connection->setToken('nope', (new \DateTimeImmutable())->add(new \DateInterval('P1D')));
        $this->api = new AppointmentApi($connection);
        $this->mockResponses([]);
    }

    private function mockResponses(array $responses): void
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $this->api->setClientHandler($stack);
    }

    public function testGetPersonClaimsByPersonUid(): void
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], self::RESPONSE),
        ]);
        $result = $this->api->getAppointmentsCursorBased();
        $resources = iterator_to_array($result->getResources());
        $this->assertCount(2, $resources);
        assert($resources[0] instanceof AppointmentResource);
        $this->assertSame('12345', $resources[0]->getCourseGroupUid());
        $this->assertSame('23456', $resources[0]->getCourseUid());
        $this->assertSame('2000-10-06T09:45:00', $resources[0]->getEndAt());
        $this->assertSame('REGULAR', $resources[0]->getEventTypeKey());
        $this->assertSame('3568', $resources[0]->getRoomUid());
        $this->assertSame('2000-10-06T08:15:00', $resources[0]->getStartAt());
        $this->assertSame('CONFIRMED', $resources[0]->getStatusTypeKey());
        $this->assertSame('1234', $resources[0]->getUid());
    }
}
