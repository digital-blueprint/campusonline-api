<?php

declare(strict_types=1);

namespace Dbp\CampusonlineApi\Tests\Rest;

use Dbp\CampusonlineApi\Rest\FilterBuilder;
use PHPUnit\Framework\TestCase;

class FilterBuilderTest extends TestCase
{
    public function testBasics()
    {
        $filter = (new FilterBuilder())->eq('FOO', 'bar')->notNull('FOO')->getFilter();
        $this->assertSame('FOO-eq=bar;FOO-notNull', $filter);
        $this->assertSame([], (new FilterBuilder())->getFilters());
        $this->assertSame('', (new FilterBuilder())->getFilter());
        $this->assertSame('FOO-isNull', (new FilterBuilder())->isNull('FOO')->getFilter());
        $this->assertSame('FOO-notNull', (new FilterBuilder())->notNull('FOO')->getFilter());
        $this->assertSame('FOO-eq=42', (new FilterBuilder())->eq('FOO', '42')->getFilter());
        $this->assertSame('FOO-eqI=42', (new FilterBuilder())->eqI('FOO', '42')->getFilter());
        $this->assertSame('FOO-like=42', (new FilterBuilder())->like('FOO', '42')->getFilter());
        $this->assertSame('FOO-likeI=42', (new FilterBuilder())->likeI('FOO', '42')->getFilter());
        $this->assertSame('FOO-gt=42', (new FilterBuilder())->gt('FOO', '42')->getFilter());
        $this->assertSame('FOO-gte=42', (new FilterBuilder())->gte('FOO', '42')->getFilter());
        $this->assertSame('FOO-lt=42', (new FilterBuilder())->lt('FOO', '42')->getFilter());
        $this->assertSame('FOO-lte=42', (new FilterBuilder())->lte('FOO', '42')->getFilter());
        $this->assertSame('FOO-in=42,1', (new FilterBuilder())->in('FOO', ['42', '1'])->getFilter());
    }

    public function testDuplicateField()
    {
        $this->expectException(\ValueError::class);
        (new FilterBuilder())->eq('FOO', 'bar')->eq('FOO', 'quux');
    }

    public function testIn()
    {
        $filter = (new FilterBuilder())->in('ID', ['42', '24'])->getFilter();
        $this->assertSame('ID-in=42,24', $filter);
    }

    public function testInEmpty()
    {
        $this->expectException(\ValueError::class);
        (new FilterBuilder())->in('ID', [])->getFilter();
    }
}
