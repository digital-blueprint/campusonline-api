<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Tests\API;

use Dbp\CampusonlineApi\API\Tools;
use PHPUnit\Framework\TestCase;

class ToolsTest extends TestCase
{
    public function testValidateFilterValue()
    {
        $this->assertSame('ok', Tools::validateFilterValue('ok'));
    }

    public function testValidateFilterValueEmpty()
    {
        $this->expectException(\ValueError::class);
        Tools::validateFilterValue('');
    }

    public function testValidateFilterValueSemicolon()
    {
        $this->expectException(\ValueError::class);
        Tools::validateFilterValue('adad;saf');
    }
}
