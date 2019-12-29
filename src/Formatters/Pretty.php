<?php

namespace fey\GenDiff\Formatters\Pretty;

use function fey\GenDiff\Formatters\Helpers\stringifyIfBoolValue;

use const fey\GenDiff\Diff\{
    UNCHANGED,
    REMOVED,
    ADDED,
    CHANGED
};

function format(array $diff): string
{
    $format = function ($diff, $level) use (&$format) {
        return array_map(function ($node) use ($level, $format) {
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
                ...$format($children, $level + 1),
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
    $result = $format($diff, 0);

    return implode(PHP_EOL, [
        '{',
        ...($result),
        '}',
    ]) . PHP_EOL;
}
