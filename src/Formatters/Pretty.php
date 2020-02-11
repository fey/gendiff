<?php

namespace GenDiff\Formatters\Pretty;

use function GenDiff\Formatters\Helpers\stringifyBoolValue;

use const GenDiff\Diff\{ADDED, CHANGED, NESTED, REMOVED, UNCHANGED};

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
                        implode(
                            PHP_EOL,
                            [
                                '{',
                                ...$format($children, $level + 1),
                                makeIndent($level + 1) . '}',
                            ]
                        )
                    ),
                    UNCHANGED => fn() => formatMessage($level, MARK_SPACES, $nodeName, $oldValue),
                    REMOVED   => fn() => formatMessage($level, MARK_MINUS, $nodeName, $oldValue),
                    ADDED     => fn() => formatMessage($level, MARK_PLUS, $nodeName, $newValue),
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

function formatMessage(int $indentLevel, string $mark, string $nodeName, $value): string
{
    $stringifyComplexValue = fn($complexValue, $level) => implode(
        PHP_EOL,
        [
            '{',
            ...array_map(
                fn($value, $key) => formatMessage($indentLevel + 1, MARK_SPACES, $key, $value),
                $complexValue,
                array_keys($complexValue)
            ),
            makeIndent($level) . '}',
        ]
    );

    $typeFormats = [
        'object'  => fn($value) => $stringifyComplexValue(get_object_vars($value), $indentLevel + 1),
        'array'   => fn($value) => $stringifyComplexValue($value),
        'string'  => fn($value) => $value,
        'boolean' => fn($value) => stringifyBoolValue($value),
        'int'     => fn($value) => (string)$value,
        'NULL'    => fn($value) => null,
    ];

    return sprintf(
        '%s%s%s: %s',
        makeIndent($indentLevel),
        $mark,
        $nodeName,
        $typeFormats[gettype($value)]($value)
    );
}

function makeIndent(int $level): string
{
    return str_repeat('    ', $level);
}
