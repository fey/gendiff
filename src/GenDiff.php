<?php

namespace GenDiff;

use function GenDiff\Parsers\parse;

const CHANGED   = 'changed';
const UNCHANGED = 'unchanged';
const REMOVED   = 'removed';
const ADDED     = 'added';
const DEFAULT_FORMATTER = 'pretty';
const FORMATTERS = [
    'plain'  => 'GenDiff\Formatters\Plain\format',
    'json'   => 'GenDiff\Formatters\Json\format',
    'pretty' => 'GenDiff\Formatters\Pretty\format',
];
const PARSERS = [
    'yaml' => 'GenDiff\Parsers\parseYaml',
    'yml'  => 'GenDiff\Parsers\parseYaml',
    'json' => 'GenDiff\Parsers\parseJson',
];


function genDiff(string $filePath1, string $filePath2, ?string $formatterName): string
{
    $data1 = parse(
        file_get_contents($filePath1),
        getParserForExtension(getFileExtension($filePath1))
    );
    $data2 = parse(
        file_get_contents($filePath2),
        getParserForExtension(getFileExtension($filePath2))
    );
    $diff = makeAstDiff($data1, $data2);
    $formatDiff = getFormatter($formatterName);

    return $formatDiff($diff);
}

function makeAstDiff(array $data1, array $data2): array
{
    $diffBuilder = function ($data1, $data2) use (&$diffBuilder) {
        $nodesNames = array_keys(array_merge($data1, $data2));

        return array_map(function ($nodeName) use ($data1, $data2, $diffBuilder) {
            $oldValue = $data1[$nodeName] ?? null;
            $newValue = $data2[$nodeName] ?? null;
            $state = UNCHANGED;
            $children = null;

            if (array_key_exists($nodeName, $data1) && !array_key_exists($nodeName, $data2)) {
                $state = REMOVED;
                if (is_array($oldValue)) {
                    $children = $diffBuilder($oldValue, $oldValue);
                }
            }
            if (!array_key_exists($nodeName, $data1) && array_key_exists($nodeName, $data2)) {
                $state = ADDED;
                if (is_array($newValue)) {
                    $children = $diffBuilder($newValue, $newValue);
                }
            }
            if (array_key_exists($nodeName, $data2) && array_key_exists($nodeName, $data1)) {
                if (is_array($oldValue) && is_array($newValue)) {
                    $children = $diffBuilder($oldValue, $newValue);
                } elseif ($oldValue !== $newValue) {
                    $state = CHANGED;
                }
            }

            return [
                'name'     => $nodeName,
                'state'    => $state,
                'oldValue' => $oldValue,
                'newValue' => $newValue,
                'children' => $children,
            ];
        }, $nodesNames);
    };

    return $diffBuilder($data1, $data2);
}

function getFileExtension(string $filePath): string
{
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    return $extension;
}

function getParserForExtension($extension)
{
    return PARSERS[$extension];
}

function getFormatter($name)
{
    return FORMATTERS[$name ?? DEFAULT_FORMATTER];
}
