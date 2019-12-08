<?php

namespace fey\GenDiff;

use Symfony\Component\Yaml\Yaml;

const CHANGED = 'changed';
const UNCHANGED = 'unchanged';
const REMOVED = 'removed';
const ADDED = 'added';

function genDiff(string $filePath1, string $filePath2): string
{
    $data1 = parse($filePath1);
    $data2 = parse($filePath2);

    $diff = calcDiff($data1, $data2);
    return stringifyDiff($diff);
}

function calcDiff(array $data1, array $data2): array
{
    $keys = array_keys(array_merge($data1, $data2));

    return array_map(function ($key) use ($data1, $data2) {
        $oldValue = $data1[$key] ?? null;
        $newValue = $data2[$key] ?? null;
        $type = 'leaf';
        if (is_array($oldValue) || is_array($newValue)) {
            $type = 'tree';
            $children = calcDiff(
                $oldValue ?? $newValue,
                $newValue ?? $oldValue
            );
        }
        if (array_key_exists($key, $data1) && !array_key_exists($key, $data2)) {
            $state = 'removed';
        }
        if (!array_key_exists($key, $data1) && array_key_exists($key, $data2)) {
            $state = 'added';
        }
        if (array_key_exists($key, $data1) && array_key_exists($key, $data2)) {
            if ($oldValue === $newValue) {
                $state = 'unchanged';
            } else {
                $state = 'changed';
            }
        }

        return [
            'key'       => $key,
            'state'     => $state,
            'type'      => $type,
            'oldValue' => $oldValue,
            'newValue' => $newValue,
            'children'  => $children ?? null,
        ];
    }, $keys);
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

function stringifyDiff(array $diff): string
{
    $unchanged = '    ';
    $removed   = '  - ';
    $added     = '  + ';
    var_dump($diff);

    $result = array_reduce($diff, function ($acc, $node) use ($unchanged, $removed, $added) {
        ['state' => $state, 'newValue' => $newValue, 'oldValue' => $oldValue, 'key' => $key, 'children' => $children] = $node;
        $oldValue = stringifyValue($oldValue);
        $newValue = stringifyValue($newValue);

        if ($children) {
        } else {
            switch ($state) {
                case 'unchanged':
                    $acc[] = "{$unchanged}{$key}: {$oldValue}";
                    break;
                case 'removed':
                    $acc[] = "{$removed}{$key}: {$oldValue}";
                    break;
                case 'added':
                    $acc[] = "{$added}{$key}: {$newValue}";
                    break;
                case 'changed':
                    $acc[] = "{$added}{$key}: {$newValue}";
                    $acc[] = "{$removed}{$key}: {$oldValue}";
                    break;
                default:
                    break;
            }
        }

        return $acc;
    }, []);

    return implode(PHP_EOL, ['{', ...($result), '}']);
}


function indentString($someString, ?int $level = 0): string
{
    return str_repeat('    ', $level) . $someString;
}

function stringifyValue($value)
{
    if (is_bool($value)) {
        $val = $value ? 'true' : 'false';
        return $val;
    }

    return $value;
}
