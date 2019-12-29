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
    $renderer = function ($nodes, $nodePath) use (&$renderer) {
        return array_map(function ($node) use ($renderer, $nodePath) {
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
                UNCHANGED => fn() => empty($children) ? [] : $renderer($children, $implodedNodePath),
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
        array_filter(flatten($renderer($diff, '')))
    ) . PHP_EOL;
}
