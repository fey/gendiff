<?php

namespace GenDiff\Formatters\Plain;

use function Funct\Collection\compact as compactCollection;
use function Funct\Collection\flatten;
use function GenDiff\Formatters\Helpers\stringifyBoolValue;

use const GenDiff\Diff\{ADDED, CHANGED, NESTED, REMOVED, UNCHANGED};
use const GenDiff\Formatters\Helpers\END_OF_LINE;

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
                    stringifyValue($oldValue),
                    stringifyValue($newValue),
                ),
                ADDED     => fn() => sprintf(
                    "Property '%s' was added with value: '%s'",
                    $ascendantNodePath,
                    stringifyValue($newValue),
                ),
                NESTED    => fn() => $format($children, $ascendantNodePath),
            ];

            return $diffMessages[$type]();
        }, $nodes);
    };
    return implode(
        END_OF_LINE,
        compactCollection(flatten($format($diff, '')))
    ) . END_OF_LINE;
}


function stringifyValue($value)
{
    $complexValue = 'complex value';
    $typeFormats = [
        'object'  => fn($value) => $complexValue,
        'array'   => fn($value) => $complexValue,
        'string'  => fn($value) => $value,
        'boolean' => fn($value) => stringifyBoolValue($value),
        'integer' => fn($value) => (string)$value,
        'NULL'    => fn($value) => null,
    ];

    return $typeFormats[gettype($value)]($value);
}
