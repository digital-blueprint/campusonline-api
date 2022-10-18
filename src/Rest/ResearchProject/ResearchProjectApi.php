<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Rest\ResearchProject;

use Dbp\CampusonlineApi\Helpers\Pagination;
use Dbp\CampusonlineApi\Helpers\Paginator;
use Dbp\CampusonlineApi\Rest\ApiException;
use Dbp\CampusonlineApi\Rest\Connection;
use Dbp\CampusonlineApi\Rest\Tools;
use GuzzleHttp\Exception\RequestException;
use League\Uri\UriTemplate;
use Psr\Http\Message\ResponseInterface;

class ResearchProjectApi
{
    /**
     * Request.
     *
     * Example: https://online.tugraz.at/tug_online/pl/rest/loc_apiProjekte?$filter=TITEL-like=Aufnahme;SPRACHE-eq=DE
     *
     * Alternative Endpoint (is said to be able to handle whitespaces in filter strings):
     * https://online.tugraz.at/tug_online/pl/rest/loc_proj.getprojects?Sprache=DE&Titel=Aufnahme
     */
    private const DATA_SERVICE = 'loc_apiProjekte';

    private const EQUALS_FILTER_OPERATOR = 1;
    private const LIKE_CASE_INSENSITIVE_FILTER_OPERATOR = 2;

    private const LANG_DE = 'DE';
    private const LANG_EN = 'EN';

    private const LANGUAGE_FILTER_NAME = 'SPRACHE';
    private const ID_FILTER_NAME = 'ID';
    private const TITLE_FILTER_NAME = 'TITEL';

    /**
     * Response.
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
    public function getResearchProject(string $identifier, array $options = []): ?ResearchProjectData
    {
        $filters = [];
        $filters[] = self::getFilter(self::ID_FILTER_NAME, self::EQUALS_FILTER_OPERATOR, $identifier);

        $projectDataList = $this->getProjectDataList($filters, $options);

        assert(count($projectDataList) === 0 || count($projectDataList) === 1);

        return $projectDataList[0] ?? null;
    }

    /**
     * @throws ApiException
     */
    public function getResearchProjects(array $options): Paginator
    {
        $titleFilterValue = $options[ResearchProjectData::TITLE_SEARCH_FILTER_NAME] ?? '';
        $titleFilterValue = trim($titleFilterValue);

        $filters = [];
        $filters[] = self::getFilter(self::TITLE_FILTER_NAME, self::LIKE_CASE_INSENSITIVE_FILTER_OPERATOR, $titleFilterValue);

        return Pagination::createPaginatorFromWholeResult($this->getProjectDataList($filters, $options), $options);
    }

    /**
     * @throws ApiException
     */
    private function getProjectDataList(array $filters, array $options): array
    {
        $filters[] = self::getLanguageFilter($options);

        $uriTemplate = new UriTemplate('pl/rest/{service}/{?%24filter,%24format}');
        $uri = (string) $uriTemplate->expand([
            'service' => self::DATA_SERVICE,
            '%24filter' => implode(';', $filters),
            '%24format' => 'json',
        ]);

        $client = $this->connection->getClient();
        try {
            $response = $client->get($uri);
        } catch (RequestException $e) {
            throw Tools::createResponseError($e);
        }

        return $this->parseStudentDataResponse($response);
    }

    /**
     * @throws ApiException
     */
    private function parseStudentDataResponse(ResponseInterface $response): array
    {
        $content = (string) $response->getBody();
        try {
            $json = Tools::decodeJSON($content, true);
        } catch (\JsonException $exception) {
            throw new ApiException('json response invalid');
        }

        $projectDataList = [];
        foreach ($json['resource'] as $res) {
            $raw = $res['content']['API_PROJEKTE'];
            $projectData = new ResearchProjectData();
            $projectData->setIdentifier($raw[self::FIELD_ID] ?? null);
            $projectData->setTitle($raw[self::FIELD_TITLE] ?? null);
            $projectData->setDescription($raw[self::FIELD_DESCRIPTION] ?? null);
            $projectData->setStartDate($raw[self::FIELD_START_DATE]['value'] ?? null);
            $projectData->setEndDate($raw[self::FIELD_END_DATE]['value'] ?? null);
            $projectDataList[] = $projectData;
        }

        return $projectDataList;
    }

    /**
     * @param mixed $filterValue
     *
     * @throws ApiException
     */
    private static function getFilter(string $filterName, int $operator, $filterValue): string
    {
        switch ($operator) {
            case self::EQUALS_FILTER_OPERATOR:
                $operatorString = '-eq=';
                break;
            case self::LIKE_CASE_INSENSITIVE_FILTER_OPERATOR:
                $operatorString = '-likeI=';
                break;
            default:
                throw new ApiException('unknown filter operator '.$operator);
        }

        return $filterName.$operatorString.$filterValue;
    }

    /**
     * @throws ApiException
     */
    private static function getLanguageFilter(array $options): string
    {
        $lang = $options['lang'] ?? 'de';
        $langFilterValue = $lang === 'en' ? self::LANG_EN : self::LANG_DE;

        return self::getFilter(self::LANGUAGE_FILTER_NAME, self::EQUALS_FILTER_OPERATOR, $langFilterValue);
    }
}
