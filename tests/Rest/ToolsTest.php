<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Tests\Rest;

use Dbp\CampusonlineApi\Rest\Tools;
use PHPUnit\Framework\TestCase;

class ToolsTest extends TestCase
{
    public function testValidateFilterValueOK()
    {
        // This list was gathered by testing various inputs
        $OK = ['ℭ', 'Привет', 'öäüß', 'ok', 'OK', '0', '1234567890', '-_,', 'ⅲ', '.'];
        foreach ($OK as $value) {
            $this->assertSame($value, Tools::validateFilterValue($value));
        }
    }

    /**
     * @dataProvider providerInvalidValues
     */
    public function testValidateFilterValueInvalid($value)
    {
        $this->expectException(\ValueError::class);
        Tools::validateFilterValue($value);
    }

    public static function providerInvalidValues()
    {
        $arguments = [['']];
        // This list is not complete... just based on manual testing
        foreach (mb_str_split(' !#$&\'*+/:;=?@[]"<>\^`{}|~£+') as $char) {
            $arguments[] = [$char];
        }

        return $arguments;
    }

    public function testValidateFilterValueEmpty()
    {
        $this->expectException(\ValueError::class);
        Tools::validateFilterValue('');
    }

    public function testValidateFilterName()
    {
        $this->assertSame('OK', Tools::validateFilterName('OK'));
        $this->assertSame('NAME_MASTERORG_L1', Tools::validateFilterName('NAME_MASTERORG_L1'));
        $this->assertSame('NAME_MASTERORG_L2', Tools::validateFilterName('NAME_MASTERORG_L2'));
        $this->assertSame('NAME_MASTERORG_L1_ENGL', Tools::validateFilterName('NAME_MASTERORG_L1_ENGL'));
        $this->assertSame('NAME_MASTERORG_L2_ENGL', Tools::validateFilterName('NAME_MASTERORG_L2_ENGL'));
    }

    public function testValidateFilterNameSemicolon()
    {
        $this->expectException(\ValueError::class);
        Tools::validateFilterName('adad;saf');
    }

    public function testValidateFilterNameEmpty()
    {
        $this->expectException(\ValueError::class);
        Tools::validateFilterName('');
    }

    public function testValidateFilterNameMinus()
    {
        $this->expectException(\ValueError::class);
        Tools::validateFilterName('adad-eq');
    }

    public function testValidateFilterValueList()
    {
        $this->assertSame('foo,bar', Tools::validateFilterValueList(['foo', 'bar']));
    }

    /**
     * @throws \JsonException
     */
    public function testDecodeJSONWithIllegalControlCharacters(): void
    {
        $json = "{\"foo\": \"bar\x04\"}";
        try {
            json_decode($json, flags: JSON_THROW_ON_ERROR);
            $this->fail('Expected json_decode of string with illegal control character to throw a JSON_THROW_ON_ERROR exception');
        } catch (\JsonException $exception) {
            $this->assertEquals(JSON_ERROR_CTRL_CHAR, $exception->getCode());
        }

        $this->assertEquals(['foo' => "bar\x04"], Tools::decodeJSON($json, true));
    }
}
