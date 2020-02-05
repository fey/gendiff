<?php

namespace fey\GenDiff\Formatters\Pretty;

use function fey\GenDiff\Formatters\Helpers\stringifyIfBoolValue;

use const fey\GenDiff\Diff\{ADDED, CHANGED, NESTED, REMOVED, UNCHANGED};

const MARK_SPACES = '    ';
const MARK_MINUS  = '  - ';
const MARK_PLUS   = '  + ';

function format(array $diff): string
{
    $format = function ($diff, $level) use (&$format) {
        return array_map(
            function ($node) use ($level, $format) {
                [
                    'type'     => $type,
                    'newValue' => $newValue,
                    'oldValue' => $oldValue,
                    'name'     => $nodeName,
                    'children' => $children,
                ] = $node;

                $diffMessages = [
                    NESTED    => fn() => formatMessage(
                        $level,
                        MARK_SPACES,
                        $nodeName,
                        implode(PHP_EOL, [
                            '{',
                            ...$format($children, $level + 1),
                            makeIndent($level + 1) . '}'
                        ]),
                    ),
                    UNCHANGED => fn() => formatMessage(
                        $level,
                        MARK_SPACES,
                        $nodeName,
                        (is_object($oldValue) ? renderComplexValue(get_object_vars($oldValue), $level) : $oldValue)
                    ),
                    REMOVED   => fn() => formatMessage(
                        $level,
                        MARK_MINUS,
                        $nodeName,
                        (is_object($oldValue) ? renderComplexValue(get_object_vars($oldValue), $level + 1) : $oldValue)
                    ),
                    ADDED     => fn() => formatMessage(
                        $level,
                        MARK_PLUS,
                        $nodeName,
                        (is_object($newValue) ? renderComplexValue(get_object_vars($newValue), $level + 1) : $newValue)
                    ),
                    CHANGED   => fn() => implode(
                        PHP_EOL,
                        [
                            formatMessage($level, MARK_PLUS, $nodeName, $newValue),
                            formatMessage($level, MARK_MINUS, $nodeName, $oldValue),
                        ]
                    ),
                ];

                return $diffMessages[$type]();
            },
            is_object($diff) ? get_object_vars($diff) : $diff
        );
    };
    $result = $format($diff, 0);

    return implode(
        PHP_EOL,
        [
                '{',
                ...$result,
                '}',
            ]
    ) . PHP_EOL;
}

function formatMessage($indentLevel, $mark, $nodeName, $value)
{
    return sprintf('%s%s%s: %s', makeIndent($indentLevel), $mark, $nodeName, stringifyIfBoolValue($value));
}

function makeIndent($level)
{
    return str_repeat('    ', $level);
}

function isComplexValue($value): bool
{
    return is_object($value) || is_array($value);
}


function renderComplexValue(array $complexValue, int $level): string
{
    return implode(
        PHP_EOL,
        [
            '{',
            ...array_map(
                function ($value, $key) use ($level) {
                    return sprintf(
                        "%s%s: %s",
                        makeIndent($level + 1),
                        $key,
                        isComplexValue($value) ? renderComplexValue($value, $level + 1) : $value
                    );
                },
                $complexValue,
                array_keys($complexValue)
            ),
            makeIndent($level) . '}'
        ]
    );
}
