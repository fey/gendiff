<?php

namespace fey\GenDiff\Diff;

use function fey\GenDiff\Parsers\parse;

const CHANGED   = 'changed';
const UNCHANGED = 'unchanged';
const REMOVED   = 'removed';
const ADDED     = 'added';
const DEFAULT_FORMATTER = 'pretty';
const FORMATTERS = [
    'plain'  => 'fey\GenDiff\Formatters\Plain\format',
    'json'   => 'fey\GenDiff\Formatters\Json\format',
    'pretty' => 'fey\GenDiff\Formatters\Pretty\format',
];

function genDiff(string $filePath1, string $filePath2, ?string $formatterName): string
{
    $data1 = parse(
        file_get_contents($filePath1),
        pathinfo($filePath1, PATHINFO_EXTENSION)
    );
    $data2 = parse(
        file_get_contents($filePath2),
        pathinfo($filePath2, PATHINFO_EXTENSION)
    );
    $diff = makeAstDiff($data1, $data2);
    $formatDiff = getFormatter($formatterName);

    return $formatDiff($diff);
}

function makeAstDiff(array $data1, array $data2): array
{
    $makeAst = function ($data1, $data2) use (&$makeAst) {
        $nodesNames = array_keys(array_merge($data1, $data2));

        return array_map(function ($nodeName) use ($data1, $data2, $makeAst) {
            $oldValue = $data1[$nodeName] ?? null;
            $newValue = $data2[$nodeName] ?? null;
            $state = UNCHANGED;
            $children = null;

            if (array_key_exists($nodeName, $data1) && !array_key_exists($nodeName, $data2)) {
                $state = REMOVED;
                if (is_array($oldValue)) {
                    $children = $makeAst($oldValue, $oldValue);
                }
            }
            if (!array_key_exists($nodeName, $data1) && array_key_exists($nodeName, $data2)) {
                $state = ADDED;
                if (is_array($newValue)) {
                    $children = $makeAst($newValue, $newValue);
                }
            }
            if (array_key_exists($nodeName, $data2) && array_key_exists($nodeName, $data1)) {
                if (is_array($oldValue) && is_array($newValue)) {
                    $children = $makeAst($oldValue, $newValue);
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

    return $makeAst($data1, $data2);
}

function getFormatter($name)
{
    return FORMATTERS[$name ?? DEFAULT_FORMATTER];
}
