<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Rest\ResearchProject;

use Dbp\CampusonlineApi\Helpers\ApiException;
use Dbp\CampusonlineApi\Helpers\Pagination;
use Dbp\CampusonlineApi\Rest\Api;
use Dbp\CampusonlineApi\Rest\Connection;
use Dbp\CampusonlineApi\Rest\Tools;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use League\Uri\Contracts\UriException;
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

    private const LANG_DE = 'DE';
    private const LANG_EN = 'EN';

    private const LANGUAGE_FILTER_NAME = 'SPRACHE';
    private const ID_FILTER_NAME = 'ID';
    private const TITLE_FILTER_NAME = 'TITEL';

    private const MAX_NUM_ITEMS_PER_PAGE_MAX = 1000;

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
        $filters[] = Api::getFilter(self::ID_FILTER_NAME, Api::EQUALS_FILTER_OPERATOR, $identifier);

        $projectDataList = $this->getProjectDataList(1, 2, $filters, $options);

        assert(count($projectDataList) === 0 || count($projectDataList) === 1);

        return $projectDataList[0] ?? null;
    }

    /**
     * @throws ApiException
     */
    public function getResearchProjects(int $currentPageNumber, int $maxNumItemsPerPage, array $filters = [], array $options = []): array
    {
        $titleFilterValue = $filters[ResearchProjectData::TITLE_SEARCH_FILTER_NAME] ?? '';
        $titleFilterValue = trim($titleFilterValue);

        $apiFilters = [];
        $apiFilters[] = Api::getFilter(self::TITLE_FILTER_NAME, Api::LIKE_CASE_INSENSITIVE_FILTER_OPERATOR, $titleFilterValue);

        return $this->getProjectDataList($currentPageNumber, $maxNumItemsPerPage, $apiFilters, $options);
    }

    /**
     * @throws ApiException
     */
    private function getProjectDataList(int $currentPageNumber, int $maxNumItemsPerPage, array $filters, array $options): array
    {
        $filters[] = self::getLanguageFilter($options);

        $maxNumItemsPerPage = min($maxNumItemsPerPage, self::MAX_NUM_ITEMS_PER_PAGE_MAX);
        $top = $maxNumItemsPerPage;
        $skip = Pagination::getPageStartIndex($currentPageNumber, $maxNumItemsPerPage);

        $projectDataList = [];
        $uriTemplate = new UriTemplate('pl/rest/{service}/{?%24filter,%24top,%24skip,%24format}');

        while (true) {
            try {
                $uri = (string) $uriTemplate->expand([
                    'service' => self::DATA_SERVICE,
                    '%24filter' => implode(';', $filters),
                    '%24top' => $top,
                    '%24skip' => $skip,
                    '%24format' => 'json',
                ]);
            } catch (UriException $exception) {
                throw new ApiException('invalid URI format: '.$exception->getMessage());
            }

            $client = $this->connection->getClient();
            try {
                $response = $client->get($uri);
            } catch (GuzzleException $exception) {
                if ($exception instanceof RequestException) {
                    throw Tools::createResponseError($exception);
                } else {
                    throw new ApiException('http client error: '.$exception->getMessage());
                }
            }

            $currentProjectDataList = $this->parseStudentDataResponse($response);
            $numReturnedProjects = count($currentProjectDataList);
            $projectDataList = array_merge($projectDataList, $currentProjectDataList);

            // if the returned number is smaller than the requested number, we issue another request
            // (but only for larger numbers in order to prevent the second request being issued every time the last (non-full) page is returned)
            // assuming that CO max page size is larger. Tests showed that CO returned at least 2959 items at once.
            if ($maxNumItemsPerPage > 250 && $numReturnedProjects > 0 && $numReturnedProjects < $maxNumItemsPerPage) {
                $skip += $numReturnedProjects;
            } else {
                break;
            }
        }

        return $projectDataList;
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
        foreach ($json['resource'] as $resource) {
            $raw = $resource['content']['API_PROJEKTE'];
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
     * @throws ApiException
     */
    private static function getLanguageFilter(array $options): string
    {
        $lang = $options['lang'] ?? 'de';
        $langFilterValue = $lang === 'en' ? self::LANG_EN : self::LANG_DE;

        return Api::getFilter(self::LANGUAGE_FILTER_NAME, Api::EQUALS_FILTER_OPERATOR, $langFilterValue);
    }
}
