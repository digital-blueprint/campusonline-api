<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Rest\UCard;

use Dbp\CampusonlineApi\Rest\ApiException;
use Dbp\CampusonlineApi\Rest\Connection;
use Dbp\CampusonlineApi\Rest\Tools;
use GuzzleHttp\Exception\RequestException;
use League\Uri\Uri;
use League\Uri\UriTemplate;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class UCardApi implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const DATA_SERVICE = 'brm.pm.extension.ucardfoto';

    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return UCard[]
     */
    public function getCardsForIdentIdObfuscated(string $identIdObfuscated, ?string $cardType = null): array
    {
        $connection = $this->connection;

        // In theory we could fetch all cards, but it seems to be limited to 500, so don't expose
        // this functionality for now
        $identIdObfuscated = Tools::validateFilterValue($identIdObfuscated);
        $filters = [];
        $filters[] = 'IDENT_NR_OBFUSCATED-eq='.$identIdObfuscated;
        if ($cardType !== null) {
            $cardType = Tools::validateFilterValue($cardType);
            $filters[] = 'CARD_TYPE-eq='.$cardType;
        }

        $dataService = $connection->getDataServiceId(self::DATA_SERVICE);
        $uriTemplate = new UriTemplate('pl/rest/{service}/{?%24filter,%24format,%24ctx,%24top}');
        $uri = (string) $uriTemplate->expand([
            'service' => $dataService,
            '%24filter' => implode(';', $filters),
            '%24format' => 'json',
            '%24ctx' => 'lang=en',
            '%24top' => '-1', // return all (seems to be limited to 500 still)
        ]);

        $client = $connection->getClient();
        try {
            $response = $client->get($uri);
        } catch (RequestException $e) {
            throw Tools::createResponseError($e);
        }

        return $this->parseGetResponse($response);
    }

    /**
     * @throws ApiException
     */
    public function getCardPicture(UCard $card): UCardPicture
    {
        $connection = $this->connection;
        $dataService = $connection->getDataServiceId(self::DATA_SERVICE);
        $uriTemplate = new UriTemplate('pl/rest/{service}/content/{contentId}{?%24format,%24ctx}');
        $uri = (string) $uriTemplate->expand([
            'service' => $dataService,
            'contentId' => $card->contentId,
            '%24format' => 'json',
            '%24ctx' => 'lang=en',
        ]);

        $client = $connection->getClient();
        try {
            $response = $client->get($uri);
        } catch (RequestException $e) {
            throw Tools::createResponseError($e);
        }

        $pic = $this->parseGetContentResponse($response);

        // just to be sure
        if ($pic->id !== $card->contentId) {
            throw new ApiException("Content ID of response didn't match");
        }

        return $pic;
    }

    /**
     * @return UCard[]
     *
     *@throws ApiException
     */
    public function parseGetResponse(ResponseInterface $response): array
    {
        $content = (string) $response->getBody();
        $json = Tools::decodeJSON($content, true);

        $cards = [];
        foreach ($json['resource'] as $res) {
            $pic = $res['content']['plsqlCardPictureDto'];
            $cardType = $pic['CARD_TYPE'];
            $identIdObfuscated = $pic['IDENT_NR_OBFUSCATED'];
            $contentId = (string) $pic['CONTENT_ID'];
            $isUpdatable = $pic['IS_UPDATABLE'] === 'true';
            $contentSize = $pic['CONTENT_SIZE'];
            $cards[] = new UCard($identIdObfuscated, $cardType, $contentId, $contentSize, $isUpdatable);
        }

        return $cards;
    }

    public function parseGetContentResponse(ResponseInterface $response): UCardPicture
    {
        $content = (string) $response->getBody();
        $json = Tools::decodeJSON($content, true);

        $pic = $json['resource'][0]['content']['plsqlCardPicture'];
        $id = (string) $pic['ID'];
        $uri = Uri::createFromString($pic['CONTENT']);
        if ($uri->getScheme() !== 'data') {
            throw new ApiException('invalid content scheme');
        }
        $parts = explode(',', $uri->getPath(), 2);
        if (count($parts) !== 2) {
            throw new ApiException('Invalid content');
        }
        $content = base64_decode($parts[1], true);
        if ($content === false) {
            throw new ApiException('Invalid content');
        }

        return new UCardPicture($id, $content);
    }

    /**
     * @throws ApiException
     */
    public function createCardForIdentIdObfuscated(string $identIdObfuscated, string $cardType): void
    {
        $connection = $this->connection;
        $dataService = $connection->getDataServiceId(self::DATA_SERVICE);
        $uriTemplate = new UriTemplate('pl/rest/{service}/{?%24format,%24ctx}');
        $uri = (string) $uriTemplate->expand([
            'service' => $dataService,
            '%24format' => 'json',
            '%24ctx' => 'lang=en',
        ]);

        $client = $connection->getClient();
        try {
            $response = $client->post($uri, [
                'form_params' => [
                    'IDENT_NR_OBFUSCATED' => $identIdObfuscated,
                    'CARD_TYPE' => $cardType,
                ],
            ]);
        } catch (RequestException $e) {
            throw Tools::createResponseError($e);
        }

        $content = (string) $response->getBody();
        Tools::decodeJSON($content, true);
    }

    /**
     * @throws ApiException
     */
    public function setCardPicture(UCard $card, string $data): void
    {
        $connection = $this->connection;
        $contentId = $card->contentId;

        $dataService = $connection->getDataServiceId(self::DATA_SERVICE);
        $uriTemplate = new UriTemplate('pl/rest/{service}/content/{contentId}{?%24format,%24ctx}');
        $uri = (string) $uriTemplate->expand([
            'service' => $dataService,
            'contentId' => $contentId,
            '%24format' => 'json',
            '%24ctx' => 'lang=en',
        ]);

        $client = $connection->getClient();
        try {
            $response = $client->post($uri, [
                'multipart' => [
                    [
                        'name' => 'CONTENT',
                        'contents' => $data,
                        'filename' => 'filename.jpg',
                    ],
                ],
            ]);
        } catch (RequestException $e) {
            throw Tools::createResponseError($e);
        }

        $content = (string) $response->getBody();
        Tools::decodeJSON($content, true);
    }
}
