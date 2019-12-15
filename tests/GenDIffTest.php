<?php

namespace fey\GenDiff\Tests;

use PHP_CodeSniffer\Reports\Diff;
use PHPUnit\Framework\TestCase;

use function fey\GenDiff\genDiff;

class ParserTest extends TestCase
{
    public function testPrettyDiffJsonFlat()
    {
        $diff = genDiff(
            dirname(__DIR__) . '/examples/flat/before.json',
            dirname(__DIR__) . '/examples/flat/after.json'
        );

        $this->assertStringEqualsFile(
            dirname(__DIR__) . '/examples/flat/pretty_diff.txt',
            $diff
        );
    }

    public function testPrettyDiffYamlFlat()
    {
        $diff = genDiff(
            dirname(__DIR__) . '/examples/flat/before.json',
            dirname(__DIR__) . '/examples/flat/after.yml'
        );
        $this->assertStringEqualsFile(dirname(__DIR__) . '/examples/flat/pretty_diff.txt', $diff);
    }

    public function testPrettyDiffNestedJson()
    {
        $diff = genDiff(
            dirname(__DIR__) . '/examples/nested/before.json',
            dirname(__DIR__) . '/examples/nested/after.json'
        );
        $this->assertStringEqualsFile(
            dirname(__DIR__) . '/examples/nested/pretty_diff.txt',
            $diff
        );
    }

    public function testPlainDiffNested()
    {
        $diff = genDiff(
            dirname(__DIR__) . '/examples/nested/before.json',
            dirname(__DIR__) . '/examples/nested/after.json',
            'plain'
        );
        $this->assertStringEqualsFile(
            dirname(__DIR__) . '/examples/flat/plain_diff.txt',
            $diff
        );
    }

    public function testJsonDiffFlat()
    {
        $diff = genDiff(
            dirname(__DIR__) . '/examples/flat/before.json',
            dirname(__DIR__) . '/examples/flat/after.json',
            'json'
        );
        $this->assertStringEqualsFile(
            dirname(__DIR__) . '/examples/flat/diff.json',
            $diff
        );
    }

    public function testJsonDiffNested()
    {
        $diff = genDiff(
            dirname(__DIR__) . '/examples/nested/before.json',
            dirname(__DIR__) . '/examples/nested/after.json',
            'json'
        );
        $this->assertStringEqualsFile(
            dirname(__DIR__) . '/examples/nested/diff.json',
            $diff
        );
    }
}
