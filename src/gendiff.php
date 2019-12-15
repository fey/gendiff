<?php

namespace fey\GenDiff;

use Symfony\Component\Yaml\Yaml;

const CHANGED   = 'changed';
const UNCHANGED = 'unchanged';
const REMOVED   = 'removed';
const ADDED     = 'added';

function genDiff(string $filePath1, string $filePath2, $format = 'pretty'): string
{
    $data1 = parse($filePath1);
    $data2 = parse($filePath2);

    $diff = calcDiff($data1, $data2);

    switch ($format) {
        case 'plain':
            return plainDiff($diff);
        case 'json':
            return jsonDiff($diff);
        default:
            return prettyDiff($diff);
    }
}

function jsonDiff($diff): string
{
    return json_encode($diff, JSON_PRETTY_PRINT) . PHP_EOL;
}

function calcDiff(array $data1, array $data2): array
{
    $diffBuilder = function ($parent, $data1, $data2) use (&$diffBuilder) {
        $keys = array_keys(array_merge($data1, $data2));

        return array_map(function ($key) use ($data1, $data2, $diffBuilder, $parent) {
            $oldValue = $data1[$key] ?? null;
            $newValue = $data2[$key] ?? null;
            $state = 'unchanged';
            $children = null;

            if (array_key_exists($key, $data1) && !array_key_exists($key, $data2)) {
                $state = 'removed';
                if (is_array($oldValue)) {
                    $children = $oldValue = $diffBuilder(
                        [...$parent, $key],
                        $oldValue,
                        $oldValue
                    );
                }
            }
            if (!array_key_exists($key, $data1) && array_key_exists($key, $data2)) {
                $state = 'added';
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
                    $state = 'changed';
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
            return parseYaml($fileContent);
        case 'json':
            return parseJson($fileContent);
        default:
            return;
    }
}

function parseJson(string $data)
{
    return json_decode($data, true);
}

function parseYaml(string $data)
{
    return Yaml::parse($data, Yaml::DUMP_OBJECT_AS_MAP);
}

function prettyDiff(array $diff): string
{
    $unchanged = '    ';
    $removed   = '  - ';
    $added     = '  + ';
    $makeIndent = function ($level) {
        return str_repeat('    ', $level);
    };
    $diffBuilder = function ($diff, $level) use (&$diffBuilder, $unchanged, $removed, $added, $makeIndent) {
        $indent = $makeIndent($level);
        $acc = [];
        foreach ($diff as $node) {
            [
                'state'    => $state,
                'newValue' => $newValue,
                'oldValue' => $oldValue,
                'key'      => $key,
                'children' => $children
            ] = $node;
            $newValue = stringifyValue($newValue);
            $oldValue = stringifyValue($oldValue);
            if ($children) {
                $oldValue = $newValue = implode(PHP_EOL, [
                    '{',
                    ...$diffBuilder($children, $level + 1),
                    $makeIndent($level + 1) . '}'
                ]);
            }
            switch ($state) {
                case UNCHANGED:
                    $acc[] = "{$indent}{$unchanged}{$key}: {$oldValue}";
                    break;
                case REMOVED:
                    $acc[] = "{$indent}{$removed}{$key}: {$oldValue}";
                    break;
                case ADDED:
                    $acc[] = "{$indent}{$added}{$key}: {$newValue}";
                    break;
                case CHANGED:
                    $acc[] = "{$indent}{$added}{$key}: {$newValue}";
                    $acc[] = "{$indent}{$removed}{$key}: {$oldValue}";
                    break;
                default:
                    break;
            }
        }

        return $acc;
    };
    $result = $diffBuilder($diff, 0);

    return implode(PHP_EOL, [
        '{',
        ...($result),
        '}',
    ]) . PHP_EOL;
}

function plainDiff(array $diff): string
{
    $messages = [
        REMOVED => function (array $node) {
            return sprintf(
                "Property '%s' was removed",
                implode('path', $node['path'])
            );
        },
        ADDED   => function (array $node) {
            $node['newValue'] = $node['children']
            ? 'complex value'
            : $node['newValue'];
            return sprintf(
                "Property '%s' was added with value: '%s'",
                implode('path', $node['path']),
                $node['newValue']
            );
        },
        CHANGED => function (array $node) {
            $node['newValue'] = $node['children']
            ? 'complex value'
            : $node['newValue'];
            return sprintf(
                "Property '%s' was changed. From '%s' to '%s'",
                implode('path', $node['path']),
                $node['oldValue'],
                $node['newValue']
            );
        },
        UNCHANGED => function () {
            return '';
        },
    ];

    $differ = function ($nodes, $acc) use (&$differ) {
        return array_reduce($nodes, function ($acc, $node) use ($differ) {
            $fullPath = implode('.', $node['path']);
            switch ($node['state']) {
                case CHANGED:
                    $acc[] = sprintf(
                        "Property '%s' was changed. From '%s' to '%s'",
                        $fullPath,
                        $node['oldValue'],
                        $node['newValue']
                    );
                    break;
                case UNCHANGED:
                    if ($node['children']) {
                        return $differ($node['children'], $acc);
                    }
                    break;
                case ADDED:
                    $acc[] = sprintf(
                        "Property '%s' was added with value: '%s'",
                        $fullPath,
                        $node['children'] ? 'complex value' : $node['newValue']
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

    return implode(PHP_EOL, $differ($diff, [])) . PHP_EOL;
}



function stringifyValue($value)
{
    if (is_bool($value)) {
        $val = $value ? 'true' : 'false';
        return $val;
    }

    return $value;
}
