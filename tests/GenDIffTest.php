<?php

namespace fey\GenDiff\Tests;

use PHP_CodeSniffer\Reports\Diff;
use PHPUnit\Framework\TestCase;

use function fey\GenDiff\genDiff;

class ParserTest extends TestCase
{
    public function testDiffJsonFlat()
    {
        $diff = genDiff(__DIR__ . '/examples/flat/before.json', __DIR__ . '/examples/flat/after.json');

        $this->assertStringEqualsFile(__DIR__ . '/examples/flat/diff.txt', $diff);
    }

    public function testDiffYamlFlat()
    {
        $diff = genDiff(__DIR__ . '/examples/flat/before.json', __DIR__ . '/examples/flat/after.yml');
        $this->assertStringEqualsFile(__DIR__ . '/examples/flat/diff.txt', $diff);
    }

    public function testDiffJsonNested()
    {
        $diff = genDiff(__DIR__ . '/examples/nested/before.json', __DIR__ . '/examples/nested/after.json');
        $this->assertStringEqualsFile(__DIR__ . '/examples/nested/diff.txt', $diff);
    }

    public function testPlainDiff()
    {
        $diff = genDiff(__DIR__ . '/examples/nested/before.json', __DIR__ . '/examples/nested/after.json', 'plain');
        $this->assertEquals(<<<DIFF
Property 'common.setting2' was removed
Property 'common.setting6' was removed
Property 'common.setting4' was added with value: 'blah blah'
Property 'common.setting5' was added with value: 'complex value'
Property 'group1.baz' was changed. From 'bas' to 'bars'
Property 'group2' was removed
Property 'group3' was added with value: 'complex value'

DIFF, $diff);
    }
}
