<?php

namespace GenDiff\Tests;

use PHPUnit\Framework\TestCase;

use function GenDiff\Diff\genDiff;

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
    public function testGenDiff(string $caseFormat, string $expectedOutputFile, ?string $format): void
    {
        $diff = genDiff(
            $this->getFilepathBefore($caseFormat),
            $this->getFilepathAfter($caseFormat),
            $format
        );
        $this->assertStringEqualsFile(
            $this->getFilepathExpectedOutput($expectedOutputFile),
            $diff
        );
    }

    /**
     * @return array
     */
    public function data()
    {
        return [
            'without params yaml'         => ['yml', 'stylish', null],
            'without params json'         => ['json', 'stylish', null],
            'with stylish formatter param' => ['json', 'stylish', 'stylish'],
            'plain formatter'             => ['json', 'plain', 'plain'],
            'json formatter'              => ['json', 'json', 'json'],
        ];
    }

    /**
     * @param string $fileName
     * @return string
     */
    private function getFixturePath(): string
    {
        return __DIR__ . '/fixtures/';
    }

    private function getFilepathBefore(string $caseFormat): string
    {
        return "{$this->getFixturePath()}{$caseFormat}/before.{$caseFormat}";
    }

    private function getFilepathAfter(string $caseFormat): string
    {
        return "{$this->getFixturePath()}{$caseFormat}/after.{$caseFormat}";
    }

    private function getFilepathExpectedOutput(string $format): string
    {
        return "{$this->getFixturePath()}{$format}.txt";
    }
}
