<?php

namespace GenDiff\Tests;

use PHPUnit\Framework\TestCase;

use function GenDiff\genDiff;

class ParserTest extends TestCase
{
    /**
     * @dataProvider data
     */
    public function testGenDiff($expected, $format)
    {
        $diff = genDiff(
            $this->getFixturesDirectoryPath() . '/before.json',
            $this->getFixturesDirectoryPath() . '/after.json',
            $format
        );
        $this->assertStringEqualsFile(
            $this->getFixturesDirectoryPath() . $expected,
            $diff
        );
    }

    public function data()
    {
        return [
            ['pretty_diff.txt', 'pretty'],
            ['pretty_diff.txt', null],
            ['plain_diff.txt', 'plain' ],
            ['diff.json', 'json' ],
        ];
    }

    private function getFixturesDirectoryPath(): string
    {
        return dirname(__DIR__) . '/fixtures/';
    }
}
