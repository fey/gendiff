<?php

namespace GenDiff\Formatters\Plain;

use function Funct\Collection\compact as compactCollection;
use function Funct\Collection\flatten;
use function GenDiff\Formatters\Helpers\isComplexValue;
use function GenDiff\Formatters\Helpers\stringifyBoolValue;

use const GenDiff\Diff\{ADDED, CHANGED, NESTED, REMOVED, UNCHANGED};

use const GenDiff\Formatters\Helpers\END_OF_LINE;

const MESSAGE_REMOVED = "Property '%s' was removed";
const MESSAGE_CHANGED = "Property '%s' was changed. From '%s' to '%s'";
const MESSAGE_ADDED = "Property '%s' was added with value: '%s'";
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
                REMOVED   => fn() => sprintf(MESSAGE_REMOVED, $ascendantNodePath),
                UNCHANGED => fn() => null,
                CHANGED   => fn() => sprintf(
                    MESSAGE_CHANGED,
                    $ascendantNodePath,
                    is_bool($oldValue) ? stringifyBoolValue($oldValue) : $oldValue,
                    is_bool($newValue) ? stringifyBoolValue($newValue) : $newValue
                ),
                ADDED     => fn() => sprintf(
                    MESSAGE_ADDED,
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
        END_OF_LINE,
        compactCollection(flatten($format($diff, '')))
    ) . END_OF_LINE;
}


function formatOutputMessage(string $state, )
{

}
