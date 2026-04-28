<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Tests\PublicRestApi;

use Dbp\CampusonlineApi\PublicRestApi\Connection;
use Dbp\CampusonlineApi\PublicRestApi\Persons\PersonClaimsApi;
use Dbp\CampusonlineApi\PublicRestApi\Persons\PersonClaimsResource;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class PersonClaimsApiTest extends TestCase
{
    private $api;

    public const RESPONSE = <<<JSON
        {
          "items": [
            {
              "givenName": "John",
              "surname": "Doe",
              "uid": "A3F8B2C9E1D74F6A",
              "addresses": [
                {
                  "city": "Sample City",
                  "country": "AT",
                  "employeeAddressTypeAbbreviation": "WS",
                  "employeeAddressTypeName": {
                    "value": {
                      "de": "Wohnsitz",
                      "en": "Residence",
                      "it": null,
                      "fr": null
                    }
                  },
                  "postalCode": "7243",
                  "street": "Sample Street 12"
                },
                {
                  "city": "Sample City",
                  "country": "AT",
                  "employeeAddressTypeAbbreviation": "ZW",
                  "employeeAddressTypeName": {
                    "value": {
                      "de": "Zweitwohnsitz",
                      "en": "Secondary residence",
                      "it": null,
                      "fr": null
                    }
                  },
                  "postalCode": "7243",
                  "street": "Sample Street 12"
                }
              ],
              "businessCardUrlEmployee": "https://qline.tugraz.at/QSYSTEM_TUG/visitenkarte.show_vcard?pPersonenGruppe=3&pPersonenId=4B7E2F9A1C3D8E5F",
              "dateOfBirth": "1975-06-23",
              "email": "john.doe@student.tugraz.at",
              "emailEmployee": "john.doe@student.tugraz.at",
              "emailStudent": "john.doe@student.tugraz.at",
              "employeeInternalId": 273951,
              "genderKey": "W",
              "internalPhoneNumbersEmployee": [],
              "matriculationNumber": "05837291",
              "personGroups": [
                "STUDENT",
                "EMPLOYEE"
              ],
              "personInternalId": -582947,
              "personTypeKey": "R",
              "studentInternalId": 582947
            }
          ],
          "limit": 100,
          "nextCursor": "xxxxxxxx=="
        }
        JSON;

    protected function setUp(): void
    {
        parent::setUp();
        $connection = new Connection('http://invalid', 'clientid', 'secret');
        $connection->setToken('nope', (new \DateTimeImmutable())->add(new \DateInterval('P1D')));
        $this->api = new PersonClaimsApi($connection);
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
        $result = $this->api->getPersonClaimsByPersonUid('A3F8B2C9E1D74F6A');
        $this->assertSame('A3F8B2C9E1D74F6A', $result->getUid());
        $this->assertSame('John', $result->getGivenName());
        $this->assertSame('Doe', $result->getSurname());
        $this->assertSame('john.doe@student.tugraz.at', $result->getEmail());
        $this->assertSame('john.doe@student.tugraz.at', $result->getEmailEmployee());
        $this->assertSame('john.doe@student.tugraz.at', $result->getEmailStudent());
        $this->assertSame(null, $result->getEmailExtpers());
        $this->assertSame('05837291', $result->getMatriculationNumber());
        $this->assertSame('1975-06-23', $result->getDateOfBirth());
        $this->assertSame(null, $result->getTitlePrefix());
        $this->assertSame(null, $result->getTitleSuffix());
        $this->assertSame('W', $result->getGenderKey());
        $this->assertSame(['STUDENT', 'EMPLOYEE'], $result->getPersonGroups());
        $this->assertSame(2, $result->getNumAddresses());
        $this->assertSame('AT', $result->getAddressCountry(0));
        $this->assertSame('Sample City', $result->getAddressCity(0));
        $this->assertSame('7243', $result->getAddressPostalCode(0));
        $this->assertSame('Sample Street 12', $result->getAddressStreet(0));
        $this->assertSame('Residence', $result->getEmployeeAddressTypeNameLocalized(0, 'en'));
        $this->assertSame([
            'de' => 'Wohnsitz',
            'en' => 'Residence',
            'it' => null,
            'fr' => null,
        ], $result->getEmployeeAddressTypeName(0));
        $this->assertSame('WS', $result->getEmployeeAddressTypeAbbreviation(0));
        $this->assertSame(null, $result->getAdditionalAddressInfo(0));
        $this->assertSame('AT', $result->getAddressCountry(1));
        $this->assertSame('Sample City', $result->getAddressCity(1));
        $this->assertSame('7243', $result->getAddressPostalCode(1));
        $this->assertSame('Sample Street 12', $result->getAddressStreet(1));
        $this->assertSame('Zweitwohnsitz', $result->getEmployeeAddressTypeNameLocalized(1, 'de'));
        $this->assertSame([
            'de' => 'Zweitwohnsitz',
            'en' => 'Secondary residence',
            'it' => null,
            'fr' => null,
        ], $result->getEmployeeAddressTypeName(1));
        $this->assertSame('ZW', $result->getEmployeeAddressTypeAbbreviation(1));
        $this->assertSame(null, $result->getAdditionalAddressInfo(1));
        $this->assertSame('https://qline.tugraz.at/QSYSTEM_TUG/visitenkarte.show_vcard?pPersonenGruppe=3&pPersonenId=4B7E2F9A1C3D8E5F', $result->getBusinessCardUrlEmployee());
        $this->assertSame(null, $result->getMobilePhoneNumberEmployee());
        $this->assertSame(null, $result->getExternalPhoneNumberEmployee());
        $this->assertSame([], $result->getInternalPhoneNumbersEmployee());
        $this->assertSame(null, $result->getWwwHomepageEmployee());
    }

    public function testGetPersonClaimsPageCursorBased(): void
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], self::RESPONSE),
        ]);
        $result = $this->api->getPersonClaimsCursorBased();
        $resources = iterator_to_array($result->getResources());
        $this->assertCount(1, $resources);
        $personClaimsResource = $resources[0];
        assert($personClaimsResource instanceof PersonClaimsResource);
        $this->assertSame('A3F8B2C9E1D74F6A', $personClaimsResource->getUid());
        $this->assertSame('xxxxxxxx==', $result->getNextCursor());
    }

    public function testGetPersonClaimsPageOffsetBased(): void
    {
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], self::RESPONSE),
        ]);
        $result = $this->api->getPersonClaimsOffsetBased();
        $resources = iterator_to_array($result);
        $this->assertCount(1, $resources);
        $personClaimsResource = $resources[0];
        assert($personClaimsResource instanceof PersonClaimsResource);
        $this->assertSame('A3F8B2C9E1D74F6A', $personClaimsResource->getUid());
    }
}
