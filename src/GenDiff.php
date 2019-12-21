<?php

namespace GenDiff;

use function GenDiff\Parsers\parse;

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

    if ($isExistsFormatter) {
        return FORMATTERS[$formatterName]($diff);
    }
    return FORMATTERS[DEFAULT_FORMATTER]($diff);
}

function makeAstDiff(array $data1, array $data2): array
{
    $diffBuilder = function ($parentPath, $data1, $data2) use (&$diffBuilder) {
        $nodesNames = array_keys(array_merge($data1, $data2));

        return array_map(function ($nodeName) use ($data1, $data2, $diffBuilder, $parentPath) {
            $nodePath = [...$parentPath, $nodeName];
            $oldValue = $data1[$nodeName] ?? null;
            $newValue = $data2[$nodeName] ?? null;
            $state = UNCHANGED;
            $children = null;

            if (array_key_exists($nodeName, $data1) && !array_key_exists($nodeName, $data2)) {
                $state = REMOVED;
                if (is_array($oldValue)) {
                    $children = $diffBuilder($nodePath, $oldValue, $oldValue);
                }
            }
            if (!array_key_exists($nodeName, $data1) && array_key_exists($nodeName, $data2)) {
                $state = ADDED;
                if (is_array($newValue)) {
                    $children = $diffBuilder($nodePath, $newValue, $newValue);
                }
            }
            if (array_key_exists($nodeName, $data2) && array_key_exists($nodeName, $data1)) {
                if (is_array($oldValue) && is_array($newValue)) {
                    $children = $diffBuilder($nodePath, $oldValue, $newValue);
                } elseif ($oldValue !== $newValue) {
                    $state = CHANGED;
                }
            }

            return [
                'name'     => $nodeName,
                'path'     => $nodePath,
                'state'    => $state,
                'oldValue' => $oldValue,
                'newValue' => $newValue,
                'children' => $children,
            ];
        }, $nodesNames);
    };

    return $diffBuilder([], $data1, $data2);
}
