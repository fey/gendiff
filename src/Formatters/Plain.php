<?php

namespace GenDiff\Formatters\Plain;

use function Funct\Collection\compact as compactCollection;
use function Funct\Collection\flatten;
use function GenDiff\Formatters\Helpers\stringifyBoolValue;
use function GenDiff\Formatters\Helpers\isComplexValue;

use const GenDiff\Diff\{ADDED, CHANGED, NESTED, REMOVED, UNCHANGED};

function format(array $diff): string
{
    $format = function ($nodes, $nodePath) use (&$format) {
        return array_map(function ($node) use ($format, $nodePath) {
            [
                'type'     => $type,
                'name'     => $name,
                'newValue' => $newValue,
                'oldValue' => $oldValue,
                'children' => $children
            ] = $node;
            $ascendantNodePath = implode('.', array_filter([$nodePath, $name]));
            $diffMessages     = [
                REMOVED   => fn() => sprintf("Property '%s' was removed", $ascendantNodePath),
                UNCHANGED => fn() => null,
                CHANGED   => fn() => sprintf(
                    "Property '%s' was changed. From '%s' to '%s'",
                    $ascendantNodePath,
                    is_bool($oldValue) ? stringifyBoolValue($oldValue) : $oldValue,
                    is_bool($newValue) ? stringifyBoolValue($newValue) : $newValue
                ),
                ADDED     => fn() => sprintf(
                    "Property '%s' was added with value: '%s'",
                    $ascendantNodePath,
                    isComplexValue($newValue) ? 'complex value' : (
                        is_bool($newValue) ? stringifyBoolValue($newValue) : $newValue
                    )
                ),
                NESTED    => fn() => $format($children, $ascendantNodePath),
            ];

            return $diffMessages[$type]();
        }, $nodes);
    };
    return implode(
        PHP_EOL,
        compactCollection(flatten($format($diff, '')))
    ) . PHP_EOL;
}
