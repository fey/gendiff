<?php

namespace GenDiff;

use function GenDiff\Parsers\parse;
use function GenDiff\Formatters\Json\format as formatJson;
use function GenDiff\Formatters\Pretty\format as formatPretty;
use function GenDiff\Formatters\Plain\format as formatPlain;

const CHANGED   = 'changed';
const UNCHANGED = 'unchanged';
const REMOVED   = 'removed';
const ADDED     = 'added';
const FORMATTERS = [
    'plain'  => 'GenDiff\Formatters\Plain\format',
    'json'   => 'GenDiff\Formatters\Json\format',
    'pretty' => 'GenDiff\Formatters\Pretty\format',
];
const DEFAULT_FORMATTER = 'pretty';

function genDiff(string $filePath1, string $filePath2, $formatterName = DEFAULT_FORMATTER): string
{
    $data1 = parse($filePath1);
    $data2 = parse($filePath2);
    $diff = makeAstDiff($data1, $data2);

    $isExistsFormatter = array_key_exists($formatterName, FORMATTERS) && function_exists(FORMATTERS[$formatterName]);

    return $isExistsFormatter
        ? FORMATTERS[$formatterName]($diff)
        : FORMATTERS[DEFAULT_FORMATTER]($diff);
}

function makeAstDiff(array $data1, array $data2): array
{
    $diffBuilder = function ($parent, $data1, $data2) use (&$diffBuilder) {
        $keys = array_keys(array_merge($data1, $data2));

        return array_map(function ($key) use ($data1, $data2, $diffBuilder, $parent) {
            $oldValue = $data1[$key] ?? null;
            $newValue = $data2[$key] ?? null;
            $state = UNCHANGED;
            $children = null;

            if (array_key_exists($key, $data1) && !array_key_exists($key, $data2)) {
                $state = REMOVED;
                if (is_array($oldValue)) {
                    $children = $oldValue = $diffBuilder(
                        [...$parent, $key],
                        $oldValue,
                        $oldValue
                    );
                }
            }
            if (!array_key_exists($key, $data1) && array_key_exists($key, $data2)) {
                $state = ADDED;
                if (is_array($newValue)) {
                    $children = $newValue = $diffBuilder(
                        [...$parent, $key],
                        $newValue,
                        $newValue
                    );
                }
            } elseif (array_key_exists($key, $data1) && array_key_exists($key, $data2)) {
                if (is_array($oldValue) && is_array($newValue)) {
                    $children = $diffBuilder(
                        [...$parent, $key],
                        $oldValue,
                        $newValue
                    );
                    $oldValue = null;
                    $newValue = null;
                } elseif ($oldValue !== $newValue) {
                    $state = CHANGED;
                }
            }

            return [
                'key'      => $key,
                'path'     => [...$parent, $key],
                'state'    => $state,
                'oldValue' => $oldValue,
                'newValue' => $newValue,
                'children' => $children,
            ];
        }, $keys);
    };

    return $diffBuilder([], $data1, $data2);
}
