<?php

namespace fey\GenDiff\Tests;

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
}
