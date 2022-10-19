<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Tests\Rest\UCard;

use Dbp\CampusonlineApi\Helpers\ApiException;
use Dbp\CampusonlineApi\Rest\Api;
use Dbp\CampusonlineApi\Rest\UCard\UCard;
use Dbp\CampusonlineApi\Rest\UCard\UCardPicture;
use Dbp\CampusonlineApi\Rest\UCard\UCardType;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class UCardTest extends TestCase
{
    public const IDENT_ID_OBFUSCATED = '1234567890';
    public const CONTENT_ID = '54321';

    private $api;

    protected function setUp(): void
    {
        $this->api = new Api('http://localhost', 'nope', 'nope');
        $this->api->getConnection()->setToken('sometoken');
        $this->mockResponses([]);
    }

    private function mockResponses(array $responses)
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $this->api->getConnection()->setClientHandler($stack);
    }

    public function testGetForIdentNoPermissions()
    {
        // from real response with wrong token
        $NO_AUTH_RESPONSE = file_get_contents(__DIR__.'/no-auth-response.json');
        $this->mockResponses([
            new Response(403, ['Content-Type' => 'application/json'], $NO_AUTH_RESPONSE),
        ]);
        $this->expectException(ApiException::class);
        $this->api->UCard()->getCardsForIdentIdObfuscated(self::IDENT_ID_OBFUSCATED);
    }

    public function testGetForIdent()
    {
        $UCARD_GET_RESPONSE = file_get_contents(__DIR__.'/ucard-get-response.json');
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], $UCARD_GET_RESPONSE),
        ]);
        $cards = $this->api->UCard()->getCardsForIdentIdObfuscated(self::IDENT_ID_OBFUSCATED);
        $this->assertCount(2, $cards);

        $this->assertInstanceOf(UCard::class, $cards[0]);
        $this->assertSame(self::IDENT_ID_OBFUSCATED, $cards[0]->identIdObfuscated);
        $this->assertSame(UCardType::STA, $cards[0]->cardType);
        $this->assertSame(self::CONTENT_ID, $cards[0]->contentId);
        $this->assertSame(17561, $cards[0]->contentSize);
        $this->assertSame(false, $cards[0]->isUpdatable);

        $this->assertInstanceOf(UCard::class, $cards[1]);
        $this->assertSame(self::IDENT_ID_OBFUSCATED, $cards[1]->identIdObfuscated);
        $this->assertSame(UCardType::BA, $cards[1]->cardType);
        $this->assertSame(self::CONTENT_ID, $cards[1]->contentId);
        $this->assertSame(6269, $cards[1]->contentSize);
        $this->assertSame(true, $cards[1]->isUpdatable);
    }

    public function testCreateCard()
    {
        $UCARD_POST_RESPONSE = file_get_contents(__DIR__.'/ucard-post-response.json');
        $this->mockResponses([
            new Response(201, ['Content-Type' => 'application/json'], $UCARD_POST_RESPONSE),
        ]);

        $this->api->UCard()->createCardForIdentIdObfuscated(self::IDENT_ID_OBFUSCATED, UCardType::STA);
        $this->assertTrue(true);
    }

    public function testCreateCardError()
    {
        $UCARD_POST_RESPONSE = file_get_contents(__DIR__.'/ucard-post-error-response.json');
        $this->mockResponses([
            new Response(422, ['Content-Type' => 'application/json'], $UCARD_POST_RESPONSE),
        ]);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessageMatches('/Not allowed for given identity and card type/');
        $this->api->UCard()->createCardForIdentIdObfuscated(self::IDENT_ID_OBFUSCATED, UCardType::STA);
    }

    public function testCreateCardExistsError()
    {
        $UCARD_POST_RESPONSE = file_get_contents(__DIR__.'/ucard-post-exists-error-response.json');
        $this->mockResponses([
            new Response(422, ['Content-Type' => 'application/json'], $UCARD_POST_RESPONSE),
        ]);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessageMatches('/already exists/');
        $this->api->UCard()->createCardForIdentIdObfuscated(self::IDENT_ID_OBFUSCATED, UCardType::STA);
    }

    public function testGetCardPicture()
    {
        $PICTURE_GET_RESPONSE = file_get_contents(__DIR__.'/picture-get-response.json');
        $this->mockResponses([
            new Response(200, ['Content-Type' => 'application/json'], $PICTURE_GET_RESPONSE),
        ]);

        $card = new UCard(self::IDENT_ID_OBFUSCATED, UCardType::STA, self::CONTENT_ID, 0, false);
        $pic = $this->api->UCard()->getCardPicture($card);
        $this->assertInstanceOf(UCardPicture::class, $pic);
        $this->assertSame(self::CONTENT_ID, $pic->id);
        $this->assertStringContainsString('foobar', (string) $pic->content);
    }

    public function testSetCardPicture()
    {
        $PICTURE_POST_RESPONSE = file_get_contents(__DIR__.'/picture-post-response.json');
        $this->mockResponses([
            new Response(201, ['Content-Type' => 'application/json'], $PICTURE_POST_RESPONSE),
        ]);
        $card = new UCard(self::IDENT_ID_OBFUSCATED, UCardType::STA, self::CONTENT_ID, 0, true);
        $this->api->UCard()->setCardPicture($card, 'foobar');
        $this->assertTrue(true);
    }

    public function testSetCardPictureError()
    {
        $PICTURE_POST_ERROR_RESPONSE = file_get_contents(__DIR__.'/picture-post-error-response.json');
        $this->mockResponses([
            new Response(405, ['Content-Type' => 'application/json'], $PICTURE_POST_ERROR_RESPONSE),
        ]);
        $card = new UCard(self::IDENT_ID_OBFUSCATED, UCardType::STA, self::CONTENT_ID, 0, false);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessageMatches('/Update of photo is not allowed/');
        $this->api->UCard()->setCardPicture($card, 'foobar');
    }
}
