<?php

namespace fey\GenDiff\Tests;

use PHPUnit\Framework\TestCase;

use function fey\GenDiff\genDiff;

class ParserTest extends TestCase
{
    public function testDiffJson()
    {
        $diff = genDiff(__DIR__ . '/data1.json', __DIR__ . '/data2.json');

        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/expected-flat-output.json', $diff);
    }

    public function testDiffYaml()
    {
        $diff = genDiff(__DIR__ . '/before.yml', __DIR__ . '/after.yml');
        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/expected-flat-output.json', $diff);
    }
}
