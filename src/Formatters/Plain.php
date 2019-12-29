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
                'state'    => $state,
                'name'     => $name,
                'newValue' => $newValue,
                'oldValue' => $oldValue,
                'children' => $children
            ] = $node;
            $implodedNodePath = implode('.', array_filter([$nodePath, $name]));
            $diffMessages = [
                REMOVED   => fn() => sprintf("Property '%s' was removed", $implodedNodePath),
                UNCHANGED => fn() => empty($children) ? [] : $format($children, $implodedNodePath),
                CHANGED   => fn() => sprintf(
                    "Property '%s' was changed. From '%s' to '%s'",
                    $implodedNodePath,
                    stringifyIfBoolValue($oldValue),
                    stringifyIfBoolValue($newValue)
                ),
                ADDED     => fn() => sprintf(
                    "Property '%s' was added with value: '%s'",
                    $implodedNodePath,
                    empty($children) ? stringifyIfBoolValue($newValue) : 'complex value'
                ),

            ];

            return $diffMessages[$state]();
        }, $nodes);
    };
    return implode(
        PHP_EOL,
        array_filter(flatten($format($diff, '')))
    ) . PHP_EOL;
}
