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
            'without params'              => ['pretty_diff.txt', null],
            'with pertty formatter param' => ['pretty_diff.txt', 'pretty'],
            'plain formatter'             => ['plain_diff.txt', 'plain' ],
            'json formatter'              => ['diff.json', 'json' ],
        ];
    }

    private function getFixturesDirectoryPath(): string
    {
        return __DIR__ . '/fixtures/';
    }
}
