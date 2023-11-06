<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Rest\Student;

use Dbp\CampusonlineApi\Helpers\ApiException;
use Dbp\CampusonlineApi\Rest\Connection;
use Dbp\CampusonlineApi\Rest\FilterBuilder;
use Dbp\CampusonlineApi\Rest\Tools;
use GuzzleHttp\Exception\GuzzleException;
use League\Uri\UriTemplate;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * @deprecated Use GenericApi instead
 */
class StudentApi implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $connection;

    private const DATA_SERVICE = 'slc.v0.studierendendaten.student_ucRest';

    private const FIELD_PERSON_ID = 'ST_PERSON_NR';
    private const FIELD_IDENT_ID = 'IDENT_NR';
    private const FIELD_FIRST_NAME = 'VORNAME';
    private const FIELD_LAST_NAME = 'NACHNAME';
    private const FIELD_IDENT_ID_OBFUSCATED = 'NR_OBFUSCATED';

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return StudentData[]
     *
     * @throws ApiException
     */
    public function getStudentDataByPersonId(string $personId): array
    {
        $filters = (new FilterBuilder())->eq(self::FIELD_PERSON_ID, $personId)->getFilters();

        return $this->getStudentDataList($filters);
    }

    /**
     * @return StudentData[]
     *
     * @throws ApiException
     */
    public function getStudentDataByIdentId(string $identId): array
    {
        $filters = (new FilterBuilder())->eq(self::FIELD_IDENT_ID, $identId)->getFilters();

        return $this->getStudentDataList($filters);
    }

    private function getStudentDataList(array $filters): array
    {
        $uriTemplate = new UriTemplate('pl/rest/{service}/{?%24filter,%24format,%24ctx,%24top}');
        $uri = (string) $uriTemplate->expand([
            'service' => $this->connection->getDataServiceId(self::DATA_SERVICE),
            '%24filter' => implode(';', $filters),
            '%24format' => 'json',
            '%24ctx' => 'lang=en',
            '%24top' => '-1',
        ]);

        $client = $this->connection->getClient();
        try {
            $response = $client->get($uri);
        } catch (GuzzleException $guzzleException) {
            throw Tools::createApiExceptionFromJsonResponse($guzzleException);
        }

        return $this->parseStudentDataResponse($response);
    }

    private function parseStudentDataResponse(ResponseInterface $response): array
    {
        $content = (string) $response->getBody();
        $json = Tools::decodeJSON($content, true);
        $dataList = [];
        foreach ($json['resource'] as $res) {
            $raw = $res['content']['plsqlStudierendendatenDto'];
            $data = new StudentData();
            $data->firstName = $raw[self::FIELD_FIRST_NAME] ?? null;
            $data->lastName = $raw[self::FIELD_LAST_NAME] ?? null;
            $data->identId = $raw[self::FIELD_IDENT_ID] ?? null;
            $data->personId = $raw[self::FIELD_PERSON_ID] ?? null;
            $data->identIdObfuscated = $raw[self::FIELD_IDENT_ID_OBFUSCATED] ?? null;
            $dataList[] = $data;
        }

        return $dataList;
    }
}
