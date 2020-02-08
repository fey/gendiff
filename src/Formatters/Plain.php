<?php

namespace GenDiff\Formatters\Plain;

use function GenDiff\Formatters\Helpers\stringifyIfBoolValue;
use function GenDiff\Formatters\Helpers\isComplexValue;
use function Funct\Collection\flatten;

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
                UNCHANGED => fn() => '',
                CHANGED   => fn() => sprintf(
                    "Property '%s' was changed. From '%s' to '%s'",
                    $ascendantNodePath,
                    stringifyIfBoolValue($oldValue),
                    stringifyIfBoolValue($newValue)
                ),
                ADDED     => fn() => sprintf(
                    "Property '%s' was added with value: '%s'",
                    $ascendantNodePath,
                    isComplexValue($newValue) ? 'complex value' : stringifyIfBoolValue($newValue)
                ),
                NESTED    => fn() => $format($children, $ascendantNodePath),
            ];

            return $diffMessages[$type]();
        }, $nodes);
    };
    return implode(
        PHP_EOL,
        array_filter(flatten($format($diff, '')))
    ) . PHP_EOL;
}

function formatRemoved(string $nodeName): string
{
    return sprintf("Property '%s' was removed", $nodeName);
}

function formatChanged($nodeName, $oldValue, $newValue): string
{
    return sprintf(
        "Property '%s' was changed. From '%s' to '%s'",
        $nodeName,
        stringifyIfBoolValue($oldValue),
        stringifyIfBoolValue($newValue)
    );
}

function formatAdded($nodeName, $newValue): string
{
        return sprintf(
            "Property '%s' was added with value: '%s'",
            $nodeName,
            isComplexValue($newValue) ? 'complex value' : stringifyIfBoolValue($newValue)
        );
}
