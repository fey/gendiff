<?php

namespace GenDiff\Formatters\Pretty;

use function GenDiff\Formatters\Helpers\stringifyIfBoolValue;

use const GenDiff\CHANGED;
use const GenDiff\UNCHANGED;
use const GenDiff\REMOVED;
use const GenDiff\ADDED;

function format(array $diff): string
{
    $formatter = function ($diff, $level) use (&$formatter) {
        return array_map(function ($node) use ($level, $formatter) {
            $markUnchanged = '    ';
            $markRemoved   = '  - ';
            $markAdded     = '  + ';
            $makeIndent = function ($level) {
                return str_repeat('    ', $level);
            };
            $indent = $makeIndent($level);
            [
                'state'    => $state,
                'newValue' => $newValue,
                'oldValue' => $oldValue,
                'name'     => $nodeName,
                'children' => $children
            ] = $node;
            $stringifyNewValue = stringifyIfBoolValue($newValue);
            $stringifyOldValue = stringifyIfBoolValue($oldValue);
            $stringifyChildren = empty($children) ? '' : implode(PHP_EOL, [
                '{',
                ...$formatter($children, $level + 1),
                $makeIndent($level + 1) . '}'
            ]);
            $diffMessages = [
                UNCHANGED   => fn() => "{$indent}{$markUnchanged}{$nodeName}: "
                    . ($stringifyChildren ?: $stringifyOldValue),
                REMOVED     => fn() => "{$indent}{$markRemoved}{$nodeName}: "
                    . ($stringifyChildren ?: $stringifyOldValue),
                ADDED       => fn() => "{$indent}{$markAdded}{$nodeName}: "
                    . ($stringifyChildren ?: $stringifyNewValue),
                CHANGED     => fn() => implode(PHP_EOL, [
                    "{$indent}{$markAdded}{$nodeName}: {$stringifyNewValue}",
                    "{$indent}{$markRemoved}{$nodeName}: {$stringifyOldValue}"
                ]),
            ];
            return $diffMessages[$state]();
        }, $diff);
    };
    $result = $formatter($diff, 0);

    return implode(PHP_EOL, [
        '{',
        ...($result),
        '}',
    ]) . PHP_EOL;
}