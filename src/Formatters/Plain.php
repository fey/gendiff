<?php

namespace fey\GenDiff\Formatters\Plain;

use function Funct\Collection\flatten;
use function fey\GenDiff\Formatters\Helpers\stringifyIfBoolValue;

use const fey\GenDiff\Diff\{
    UNCHANGED,
    REMOVED,
    ADDED,
    CHANGED
};

function format(array $diff): string
{
    $format = function ($nodes, $nodePath) use (&$format) {
        return array_map(function ($node) use ($format, $nodePath) {
            [
                'type'    => $type,
                'name'     => $name,
                'newValue' => $newValue,
                'oldValue' => $oldValue,
                'children' => $children
            ] = $node;
            $implodedNodePath = implode('.', array_filter([$nodePath, $name]));
            $diffMessages = [
                REMOVED   => fn() => formatRemoved($implodedNodePath),
                UNCHANGED => fn() => empty($children) ? [] : $format($children, $implodedNodePath),
                CHANGED   => fn() => formatChanged($implodedNodePath, $oldValue, $newValue),
                ADDED     => fn() => formatAdded($implodedNodePath, $newValue),
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
        is_array($newValue) ? 'complex value' : stringifyIfBoolValue($newValue)
    );
}
