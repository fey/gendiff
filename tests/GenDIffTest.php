<?php

namespace fey\GenDiff\Tests;

use PHPUnit\Framework\TestCase;

use function fey\GenDiff\Diff\genDiff;

class ParserTest extends TestCase
{
    /**
     * @dataProvider data
     */
    public function testGenDiff($expectedOutputFile, $format)
    {
        $diff = genDiff(
            $this->getFixturePath('/before.json'),
            $this->getFixturePath('/after.json'),
            $format
        );
        $this->assertStringEqualsFile(
            $this->getFixturePath($expectedOutputFile),
            $diff
        );
    }

    public function data(): array
    {
        return [
            'without params'              => ['pretty_diff.txt', null],
            'with pertty formatter param' => ['pretty_diff.txt', 'pretty'],
            'plain formatter'             => ['plain_diff.txt', 'plain' ],
            'json formatter'              => ['diff.json', 'json' ],
        ];
    }

    private function getFixturePath(string $fileName): string
    {
        return __DIR__ . '/fixtures/' . $fileName;
    }
}
