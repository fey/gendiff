<?php

namespace GenDiff;

use Symfony\Component\Yaml\Yaml;

const CHANGED   = 'changed';
const UNCHANGED = 'unchanged';
const REMOVED   = 'removed';
const ADDED     = 'added';

function genDiff(string $filePath1, string $filePath2, $format = 'pretty'): string
{
    $data1 = parse($filePath1);
    $data2 = parse($filePath2);
    $diff = makeAstDiff($data1, $data2);
    switch ($format) {
        case 'plain':
            return renderPlainDiff($diff);
        case 'json':
            return renderJsonDiff($diff);
        default:
            return renderPrettyDiff($diff);
    }
}

function renderJsonDiff($diff): string
{
    return json_encode($diff, JSON_PRETTY_PRINT) . PHP_EOL;
}

function makeAstDiff(array $data1, array $data2): array
{
    $diffBuilder = function ($parent, $data1, $data2) use (&$diffBuilder) {
        $keys = array_keys(array_merge($data1, $data2));

        return array_map(function ($key) use ($data1, $data2, $diffBuilder, $parent) {
            $oldValue = $data1[$key] ?? null;
            $newValue = $data2[$key] ?? null;
            $state = UNCHANGED;
            $children = null;

            if (array_key_exists($key, $data1) && !array_key_exists($key, $data2)) {
                $state = REMOVED;
                if (is_array($oldValue)) {
                    $children = $oldValue = $diffBuilder(
                        [...$parent, $key],
                        $oldValue,
                        $oldValue
                    );
                }
            }
            if (!array_key_exists($key, $data1) && array_key_exists($key, $data2)) {
                $state = ADDED;
                if (is_array($newValue)) {
                    $children = $newValue = $diffBuilder(
                        [...$parent, $key],
                        $newValue,
                        $newValue
                    );
                }
            } elseif (array_key_exists($key, $data1) && array_key_exists($key, $data2)) {
                if (is_array($oldValue) && is_array($newValue)) {
                    $children = $diffBuilder(
                        [...$parent, $key],
                        $oldValue,
                        $newValue
                    );
                    $oldValue = null;
                    $newValue = null;
                } elseif ($oldValue !== $newValue) {
                    $state = CHANGED;
                }
            }

            return [
                'key'      => $key,
                'path'     => [...$parent, $key],
                'state'    => $state,
                'oldValue' => $oldValue,
                'newValue' => $newValue,
                'children' => $children,
            ];
        }, $keys);
    };

    return $diffBuilder([], $data1, $data2);
}

function parse($filePath)
{
    $fileContent = file_get_contents($filePath);
    switch (pathinfo($filePath, PATHINFO_EXTENSION)) {
        case 'yaml':
        case 'yml':
            return Yaml::parse($fileContent, Yaml::DUMP_OBJECT_AS_MAP);
        case 'json':
            return json_decode($fileContent, true);
        default:
            return;
    }
}

function renderPrettyDiff(array $diff): string
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

function renderPlainDiff(array $diff): string
{
    $renderer = function ($nodes, $acc) use (&$renderer) {
        return array_reduce($nodes, function ($acc, $node) use ($renderer) {
            [
                'state'    => $state,
                'path'     => $path,
                'newValue' => $newValue,
                'oldValue' => $oldValue,
                'key'      => $key,
                'children' => $children
            ] = $node;
            $fullPath = implode('.', $path);
            switch ($state) {
                case CHANGED:
                    $acc[] = sprintf(
                        "Property '%s' was changed. From '%s' to '%s'",
                        $fullPath,
                        stringifyIfBoolValue($oldValue),
                        stringifyIfBoolValue($newValue)
                    );
                    break;
                case UNCHANGED:
                    if ($children) {
                        return $renderer($children, $acc);
                    }
                    break;
                case ADDED:
                    $acc[] = sprintf(
                        "Property '%s' was added with value: '%s'",
                        $fullPath,
                        $children ? 'complex value' : stringifyIfBoolValue($newValue)
                    );
                    break;
                case REMOVED:
                    $acc[] = sprintf(
                        "Property '%s' was removed",
                        $fullPath,
                    );
                    break;
                default:
                    break;
            }

            return $acc;
        }, $acc);
    };

    return implode(PHP_EOL, $renderer($diff, [])) . PHP_EOL;
}

function stringifyIfBoolValue($value)
{
    if (is_bool($value)) {
        $val = $value ? 'true' : 'false';
        return $val;
    }

    return $value;
}
