<?php

namespace fey\GenDiff\Tests;

use PHPUnit\Framework\TestCase;

use function fey\GenDiff\Diff\genDiff;

class GenDiffTest extends TestCase
{
    /**
     * @param string $before
     * @param string $after
     * @param string $expectedOutputFile
     * @param string|null $format
     *
     * @dataProvider data
     */
    public function testGenDiff(string $before, string $after, string $expectedOutputFile, ?string $format): void
    {
        $diff = genDiff(
            $this->getFixturePath($before),
            $this->getFixturePath($after),
            $format
        );
        $this->assertStringEqualsFile(
            $this->getFixturePath($expectedOutputFile),
            $diff
        );
    }

    /**
     * @return array
     */
    public function data(): array
    {
        return [
            'diff flat json'              => ['flat_before.json', 'flat_after.json', 'flat_diff.txt', null],
            'without params yaml'         => ['before.yml', 'after.yml', 'yaml.txt', null],
            'without params json'         => ['before.json', 'after.json', 'pretty_diff.txt', null],
            'with pretty formatter param' => ['before.json', 'after.json', 'pretty_diff.txt', 'pretty'],
            //'plain formatter'             => ['before.json', 'after.json', 'plain_diff.txt', 'plain'],
            'json formatter'              => ['before.json', 'after.json', 'diff.json', 'json'],
        ];
    }

    /**
     * @param string $fileName
     * @return string
     */
    private function getFixturePath(string $fileName): string
    {
        return __DIR__ . '/fixtures/' . $fileName;
    }
}
