<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Rest\ResearchProject;

use Dbp\CampusonlineApi\Helpers\FullPaginator;
use Dbp\CampusonlineApi\Helpers\Paginator;
use Dbp\CampusonlineApi\Rest\ApiException;
use Dbp\CampusonlineApi\Rest\Connection;
use Dbp\CampusonlineApi\Rest\Tools;
use GuzzleHttp\Exception\RequestException;
use League\Uri\UriTemplate;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class ResearchProjectApi implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Request.
     */
    private const EQUALS_FILTER_OPERATOR = 1;
    private const LIKE_FILTER_OPERATOR = 2;

    private const LANG_DE = 'DE';
    private const LANG_EN = 'EN';

    // https://online.tugraz.at/tug_online/pl/rest/loc_apiProjekte?access_token=xxxxxxx&$filter=TITEL-like=Aufnahme;SPRACHE-eq=DE
    private const DATA_SERVICE = 'loc_apiProjekte';
    private const LANGUAGE_FILTER_NAME = 'SPRACHE';
    private const ID_FILTER_NAME = 'SPRACHE';

    // https://online.tugraz.at/tug_online/pl/rest/loc_proj.getprojects?access_token=xxxxxx&Sprache=DE&Titel=Aufnahme
    //private const DATA_SERVICE = 'loc_proj.getprojects'
    //private const LANGUAGE_PARAMETER_NAME = 'Sprache';

    /**
     * Respone.
     */
    private const FIELD_ID = 'ID';
    private const FIELD_TITLE = 'TITEL';
    private const FIELD_DESCRIPTION = 'BESCHREIBUNG';
    private const FIELD_START_DATE = 'BEGINN';
    private const FIELD_END_DATE = 'ENDE';

    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @throws ApiException
     */
    public function getResearchProject(string $identifier, array $options = []): ResearchProjectData
    {
        $lang = $options['lang'] ?? 'de';
        $langFilter = $lang === 'en' ? self::LANG_EN : self::LANG_DE;
        $service = self::DATA_SERVICE;

        $filters = [];
        $filters[] = self::getFilterValue(self::LANGUAGE_FILTER_NAME, self::EQUALS_FILTER_OPERATOR, $langFilter);
        $filters[] = self::getFilterValue(self::ID_FILTER_NAME, self::EQUALS_FILTER_OPERATOR, $identifier);

        $uriTemplate = new UriTemplate('pl/rest/{service}/{?%24filter,%24format,%24ctx,%24top}');
        $uri = (string) $uriTemplate->expand([
            'service' => $service,
            '%24filter' => implode(';', $filters),
        ]);

        $client = $this->connection->getClient();
        try {
            $response = $client->get($uri);
        } catch (RequestException $e) {
            throw Tools::createResponseError($e);
        }

        $projectDataList = $this->parseStudentDataResponse($response);

        $numProjects = count($projectDataList);
        if ($numProjects === 0) {
            throw new ApiException('id not found');
        }

        assert($numProjects === 1);

        return $projectDataList[0];
    }

    /**
     * @throws ApiException
     */
    public function getResearchProjects(array $options): Paginator
    {
        $lang = $options['lang'] ?? 'de';
        $langFilter = $lang === 'en' ? self::LANG_EN : self::LANG_DE;
        $service = self::DATA_SERVICE;
        $titleFilterValue = 'Aufnahme';

        $filters = [];
        $filters[] = self::getFilterValue(self::LANGUAGE_FILTER_NAME, self::EQUALS_FILTER_OPERATOR, $lang);
        $filters[] = self::getFilterValue(self::FIELD_TITLE, self::LIKE_FILTER_OPERATOR, $titleFilterValue);

        $uriTemplate = new UriTemplate('pl/rest/{service}/{?%24filter,%24format,%24ctx,%24top}');
        $uri = (string) $uriTemplate->expand([
            'service' => $service,
            '%24filter' => implode(';', $filters),
        ]);

        $client = $this->connection->getClient();
        try {
            $response = $client->get($uri);
        } catch (RequestException $e) {
            throw Tools::createResponseError($e);
        }

        $projectDataList = $this->parseStudentDataResponse($response);

        return new FullPaginator($projectDataList, 1, count($projectDataList), count($projectDataList));
    }

    /**
     * @param mixed $filterValue
     *
     * @throws ApiException
     */
    private static function getFilterValue(string $filterName, int $operator, $filterValue): string
    {
        switch ($operator) {
            case self::EQUALS_FILTER_OPERATOR:
                $operatorString = '-eq=';
                break;
            case self::LIKE_FILTER_OPERATOR:
                $operatorString = '-like=';
                break;
            default:
                throw new ApiException('unknown filter operator '.$operator);
        }

        return $filterName.$operatorString.$filterValue;
    }

    /**
     * @throws ApiException
     */
    public function parseStudentDataResponse(ResponseInterface $response): array
    {
        $content = (string) $response->getBody();
        try {
            $json = Tools::decodeJSON($content, true);
        } catch (\JsonException $exception) {
            throw new ApiException('json response invalid');
        }

        $projectDataList = [];
        foreach ($json['resource'] as $res) {
            $raw = $res['content']['plsqlStudierendendatenDto'];
            $projectData = new ResearchProjectData();
            $projectData->setIdentifier($raw[self::FIELD_ID] ?? '');
            $projectData->setTitle($raw[self::FIELD_TITLE] ?? '');
            $projectData->setDescription($raw[self::FIELD_DESCRIPTION] ?? '');
            $projectData->setStartDate($raw[self::FIELD_START_DATE] ?? '');
            $projectData->setEndDate($raw[self::FIELD_END_DATE] ?? '');
            $projectDataList[] = $projectData;
        }

        return $projectDataList;
    }
}
