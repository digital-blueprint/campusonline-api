<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Student;

use Dbp\CampusonlineApi\API\Connection;
use Dbp\CampusonlineApi\API\Tools;
use GuzzleHttp\Exception\RequestException;
use League\Uri\UriTemplate;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class StudentAPI implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $connection;

    private const DATA_SERVICE = 'slc.v0.studierendendaten.student_ucRest';

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return StudentData[]
     */
    public function getStudentDataByPersonId(string $personId): array
    {
        $connection = $this->connection;

        $personId = Tools::validateFilterValue($personId);
        $service = $connection->getDataServiceId(self::DATA_SERVICE);
        $filters[] = 'ST_PERSON_NR-eq='.$personId;
        $uriTemplate = new UriTemplate('pl/rest/{service}/{?%24filter,%24format,%24ctx,%24top}');
        $uri = (string) $uriTemplate->expand([
            'service' => $service,
            '%24filter' => implode(';', $filters),
            '%24format' => 'json',
            '%24ctx' => 'lang=en',
            '%24top' => '-1',
        ]);

        $client = $connection->getClient();
        try {
            $response = $client->get($uri);
        } catch (RequestException $e) {
            throw Tools::createResponseError($e);
        }

        return $this->parseStudentDataResponse($response);
    }

    public function parseStudentDataResponse(ResponseInterface $response): array
    {
        $content = (string) $response->getBody();
        $json = Tools::decodeJSON($content, true);
        $dataList = [];
        foreach ($json['resource'] as $res) {
            $raw = $res['content']['plsqlStudierendendatenDto'];
            $data = new StudentData();
            $data->firstName = $raw['VORNAME'] ?? null;
            $data->lastName = $raw['NACHNAME'] ?? null;
            $data->identId = $raw['IDENT_NR'] ?? null;
            $data->personId = $raw['ST_PERSON_NR'] ?? null;
            $data->identIdObfuscated = $raw['NR_OBFUSCATED'] ?? null;
            $dataList[] = $data;
        }

        return $dataList;
    }
}
