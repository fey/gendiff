<?php

namespace fey\GenDiff\Formatters\Pretty;

use function fey\GenDiff\Formatters\Helpers\stringifyIfBoolValue;

use const fey\GenDiff\Diff\{ADDED, CHANGED, REMOVED, UNCHANGED, NESTED};

const MARK_SPACES = '    ';
const MARK_MINUS = '  - ';
const MARK_PLUS = '  + ';

function format(array $diff): string
{
    $format = function ($diff, $level) use (&$format) {
        return array_map(function ($node) use ($level, $format) {
            [
                'type'     => $type,
                'newValue' => $newValue,
                'oldValue' => $oldValue,
                'name'     => $nodeName,
                'children' => $children,
            ] = $node;

            $diffMessages      = [
                NESTED    => fn() => formatMessage($level, MARK_SPACES, $nodeName, $format($children, $level)),
                UNCHANGED => fn() => formatMessage($level, MARK_SPACES, $nodeName, $oldValue),
                REMOVED   => fn() => formatMessage($level, MARK_MINUS, $nodeName, $oldValue),
                ADDED     => fn() => formatMessage($level, MARK_PLUS, $nodeName, $newValue),
                CHANGED   => fn() => implode(PHP_EOL, [
                    formatMessage($level, MARK_PLUS, $nodeName, $newValue),
                    formatMessage($level, MARK_MINUS, $nodeName, $oldValue),
                ]),
            ];
            return $diffMessages[$type]();
        }, $diff);
    };
    $result = $format($diff, 0);

    return implode(PHP_EOL, [
            '{',
            ...$result,
            '}',
        ]) . PHP_EOL;
}

function formatMessage($indentLevel, $mark, $nodeName, $value)
{
    return sprintf('%s%s%s: %s', makeIndent($indentLevel), $mark, $nodeName, stringifyIfBoolValue($value));
}

function makeIndent($level)
{
    return str_repeat('    ', $level);
}

function stringifyComplexValue($value)
{
    return array_reduce(function () {});
}
