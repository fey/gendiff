<?php

namespace fey\GenDiff\Tests;

use PHPUnit\Framework\TestCase;

use function fey\GenDiff\genDiff;

class ParserTest extends TestCase
{
    public function testGenDiffFlatFiles()
    {
        $diff = genDiff(__DIR__ . DIRECTORY_SEPARATOR . 'data1.json', __DIR__ . DIRECTORY_SEPARATOR . 'data2.json');

        $expected = <<<'DIFF'
{
    "host": "hexlet.io",
    "- timeout": 50,
    "+ timeout": 20,
    "- proxy": "123.234.53.22",
    "+ verbose": true
}
DIFF;
        $this->assertEquals($expected, $diff);
    }
}
