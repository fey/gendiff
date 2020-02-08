<?php

namespace GenDiff\Diff;

use function GenDiff\Formatters\Json\format as formatJson;
use function GenDiff\Formatters\Plain\format as formatPlain;
use function GenDiff\Formatters\Pretty\format as formatPretty;
use function GenDiff\Parsers\parse;

const CHANGED           = 'changed';
const UNCHANGED         = 'unchanged';
const REMOVED           = 'removed';
const ADDED             = 'added';
const NESTED            = 'nested';
const DEFAULT_FORMATTER = 'pretty';

function genDiff(string $filePath1, string $filePath2, ?string $formatterName): string
{
    $data1      = parse(
        file_get_contents($filePath1),
        pathinfo($filePath1, PATHINFO_EXTENSION)
    );
    $data2      = parse(
        file_get_contents($filePath2),
        pathinfo($filePath2, PATHINFO_EXTENSION)
    );
    $diff       = makeAstDiff($data1, $data2);
    $formatDiff = getFormatter($formatterName);

    return $formatDiff($diff);
}

function makeAstDiff($data1, $data2): array
{
    $makeDiff = function ($data1, $data2) use (&$makeDiff) {
        $nodesNames = array_keys(array_merge((array)$data1, (array)$data2));

        return array_map(
            function ($nodeName) use (&$makeDiff, $data1, $data2) {
                if (property_exists($data1, $nodeName) && property_exists($data2, $nodeName)) {
                    if ($data1->$nodeName === $data2->$nodeName) {
                        $type = UNCHANGED;
                    } elseif (is_object($data1->$nodeName) && is_object($data2->$nodeName)) {
                        $type = NESTED;
                    } else {
                        $type = CHANGED;
                    }
                } elseif (!property_exists($data1, $nodeName) && property_exists($data2, $nodeName)) {
                    $type = ADDED;
                } elseif (property_exists($data1, $nodeName) && !property_exists($data2, $nodeName)) {
                    $type = REMOVED;
                }

                return [
                    'type' => $type,
                    'name' => $nodeName,
                    'oldValue' => $data1->$nodeName ?? null,
                    'newValue' => $data2->$nodeName ?? null,
                    'children' => $type === NESTED ? $makeDiff($data1->$nodeName, $data2->$nodeName) : null,
                ];
            }, $nodesNames);
    };

    return $makeDiff($data1, $data2);
}

function getFormatter($name)
{
    $formatters = [
        'plain'  => fn($diff) => formatPlain($diff),
        'json'   => fn($diff) => formatJson($diff),
        'pretty' => fn($diff) => formatPretty($diff),
    ];

    return $formatters[$name ?? DEFAULT_FORMATTER];
}
