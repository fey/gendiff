<?php

namespace GenDiff\Formatters\Pretty;

use function GenDiff\Formatters\Helpers\stringifyIfBoolValue;

use const GenDiff\CHANGED;
use const GenDiff\UNCHANGED;
use const GenDiff\REMOVED;
use const GenDiff\ADDED;

function format(array $diff): string
{
    $diffBuilder = function ($diff, $level) use (&$diffBuilder) {
        return array_map(function ($node) use ($level, $diffBuilder) {
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
                'key'      => $key,
                'children' => $children
            ] = $node;
            $newValue = stringifyIfBoolValue($newValue);
            $oldValue = stringifyIfBoolValue($oldValue);
            if ($children) {
                $oldValue = $newValue = implode(PHP_EOL, [
                    '{',
                    ...$diffBuilder($children, $level + 1),
                    $makeIndent($level + 1) . '}'
                ]);
            }
            switch ($state) {
                case UNCHANGED:
                    return "{$indent}{$markUnchanged}{$key}: {$oldValue}";
                case REMOVED:
                    return "{$indent}{$markRemoved}{$key}: {$oldValue}";
                case ADDED:
                    return "{$indent}{$markAdded}{$key}: {$newValue}";
                case CHANGED:
                    return implode(PHP_EOL, [
                        "{$indent}{$markAdded}{$key}: {$newValue}",
                        "{$indent}{$markRemoved}{$key}: {$oldValue}"
                    ]);
                default:
                    return;
            }
        }, $diff);
    };
    $result = $diffBuilder($diff, 0);

    return implode(PHP_EOL, [
        '{',
        ...($result),
        '}',
    ]) . PHP_EOL;
}
