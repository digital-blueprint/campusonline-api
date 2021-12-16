<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\UCard;

use Dbp\CampusonlineApi\API\Tools;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use League\Uri\Uri;
use League\Uri\UriTemplate;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class UCardAPI implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var ?string
     */
    private $token;
    /**
     * @var ?string
     */
    private $baseUrl;
    private $clientHandler;
    private $dataService;

    public function __construct()
    {
        $this->token = null;
        $this->baseUrl = null;
        $this->dataService = 'brm.pm.extension.ucardfoto';
    }

    public function setBaseUrl(string $url): void
    {
        $this->baseUrl = $url;
    }

    public function setDataService(string $name): void
    {
        $this->dataService = $name;
    }

    /**
     * @throws UCardException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    public function fetchToken(string $clientId, string $clientSecret): void
    {
        if ($this->baseUrl === null) {
            throw new \ValueError('baseUrl is not set');
        }

        $stack = HandlerStack::create($this->clientHandler);
        $client_options = [
            'handler' => $stack,
        ];
        if ($this->logger !== null) {
            $stack->push(Tools::createLoggerMiddleware($this->logger));
        }
        $client = new Client($client_options);

        try {
            $response = $client->post($this->baseUrl.'/wbOAuth2.token', [
                'form_params' => [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'client_credentials',
                ],
            ]);
        } catch (RequestException $e) {
            throw new UCardException($e->getMessage());
        }
        $data = $response->getBody()->getContents();

        $token = Tools::decodeJSON($data, true);
        $this->setToken($token['access_token']);
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function setClientHandler(?object $handler): void
    {
        $this->clientHandler = $handler;
    }

    private function getClient(): Client
    {
        if ($this->baseUrl === null) {
            throw new \ValueError('baseUrl is not set');
        }
        if ($this->token === null) {
            throw new \ValueError('token is not set');
        }
        $stack = HandlerStack::create($this->clientHandler);
        $base_uri = $this->baseUrl;
        if (substr($base_uri, -1) !== '/') {
            $base_uri .= '/';
        }

        $client_options = [
            'base_uri' => $base_uri,
            'handler' => $stack,
            'headers' => [
                'Authorization' => 'Bearer '.$this->token,
                'Accept' => 'application/json',
            ],
        ];

        if ($this->logger !== null) {
            $stack->push(Tools::createLoggerMiddleware($this->logger));
        }

        $client = new Client($client_options);

        return $client;
    }

    /**
     * @return UCard[]
     */
    public function getCardsForIdent(string $ident, ?string $cardType = null): array
    {
        // Filtering breaks if the value is empty, so don't allow
        if (strlen($ident) === 0) {
            throw new \ValueError('empty ident not allowed');
        }

        // In theory we could fetch all cards, but it seems to be limited to 500, so don't expose
        // this functionality for now
        $filters[] = 'IDENT_NR_OBFUSCATED-eq='.$ident;
        if ($cardType !== null) {
            if (strlen($cardType) === 0) {
                throw new \ValueError('empty cardType not allowed');
            }
            $filters[] = 'CARD_TYPE-eq='.$cardType;
        }

        $uriTemplate = new UriTemplate('pl/rest/{service}/{?%24filter,%24format,%24ctx,%24top}');
        $uri = (string) $uriTemplate->expand([
            'service' => $this->dataService,
            '%24filter' => implode(';', $filters),
            '%24format' => 'json',
            '%24ctx' => 'lang=en',
            '%24top' => '-1', // return all (seems to be limited to 500 still)
        ]);

        $client = $this->getClient();
        try {
            $response = $client->get($uri);
        } catch (RequestException $e) {
            throw $this->_getResponseError($e);
        }

        return $this->parseGetResponse($response);
    }

    /**
     * @throws UCardException
     */
    public function getCardPicture(UCard $card): UCardPicture
    {
        $uriTemplate = new UriTemplate('pl/rest/{service}/content/{contentId}{?%24format,%24ctx}');
        $uri = (string) $uriTemplate->expand([
            'service' => $this->dataService,
            'contentId' => $card->contentId,
            '%24format' => 'json',
            '%24ctx' => 'lang=en',
        ]);

        $client = $this->getClient();
        try {
            $response = $client->get($uri);
        } catch (RequestException $e) {
            throw $this->_getResponseError($e);
        }

        $pic = $this->parseGetContentResponse($response);

        // just to be sure
        if ($pic->id !== $card->contentId) {
            throw new UCardException("Content ID of response didn't match");
        }

        return $pic;
    }

    /**
     * @throws UCardException
     *
     * @return UCard[]
     */
    public function parseGetResponse(ResponseInterface $response): array
    {
        $content = (string) $response->getBody();
        $json = Tools::decodeJSON($content, true);

        $cards = [];
        foreach ($json['resource'] as $res) {
            $pic = $res['content']['plsqlCardPictureDto'];
            $cardType = $pic['CARD_TYPE'];
            $ident = $pic['IDENT_NR_OBFUSCATED'];
            $contentId = (string) $pic['CONTENT_ID'];
            $isUpdatable = $pic['IS_UPDATABLE'] === 'true';
            $contentSize = $pic['CONTENT_SIZE'];
            $cards[] = new UCard($ident, $cardType, $contentId, $contentSize, $isUpdatable);
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
            throw new UCardException('invalid content scheme');
        }
        $parts = explode(',', $uri->getPath(), 2);
        if (count($parts) !== 2) {
            throw new UCardException('Invalid content');
        }
        $content = base64_decode($parts[1], true);
        if ($content === false) {
            throw new UCardException('Invalid content');
        }

        return new UCardPicture($id, $content);
    }

    public function _getResponseError(RequestException $e): UCardException
    {
        $error = Tools::createResponseError($e);

        return new UCardException($error->getMessage());
    }

    /**
     * @throws UCardException
     */
    public function createCardForIdent(string $ident, string $cardType): void
    {
        $uriTemplate = new UriTemplate('pl/rest/{service}/{?%24format,%24ctx}');
        $uri = (string) $uriTemplate->expand([
            'service' => $this->dataService,
            '%24format' => 'json',
            '%24ctx' => 'lang=en',
        ]);

        $client = $this->getClient();
        try {
            $response = $client->post($uri, [
                'form_params' => [
                    'IDENT_NR_OBFUSCATED' => $ident,
                    'CARD_TYPE' => $cardType,
                ],
            ]);
        } catch (RequestException $e) {
            throw $this->_getResponseError($e);
        }

        $content = (string) $response->getBody();
        Tools::decodeJSON($content, true);
    }

    /**
     * @throws UCardException
     */
    public function setCardPicture(UCard $card, string $data): void
    {
        $contentId = $card->contentId;

        $uriTemplate = new UriTemplate('pl/rest/{service}/content/{contentId}{?%24format,%24ctx}');
        $uri = (string) $uriTemplate->expand([
            'service' => $this->dataService,
            'contentId' => $contentId,
            '%24format' => 'json',
            '%24ctx' => 'lang=en',
        ]);

        $client = $this->getClient();
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
            throw $this->_getResponseError($e);
        }

        $content = (string) $response->getBody();
        Tools::decodeJSON($content, true);
    }
}
