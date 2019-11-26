<?php

namespace fey\GenDiff;

use Symfony\Component\Yaml\Yaml;

function genDiff(string $filePath1, string $filePath2): string
{
    $data1 = parse($filePath1);
    $data2 = parse($filePath2);

    $diff = calcDiff($data1, $data2);
    return stringifyDiff($diff);
}

function calcDiff(array $data1, array $data2): array
{
    $keys = array_merge(array_keys($data1), array_keys($data2));
    $diff = array_reduce($keys, function ($acc, $key) use ($data1, $data2) {
        $oldValue = $data1[$key] ?? null;
        $newValue = $data2[$key] ?? null;
        if (array_key_exists($key, $data1) && !array_key_exists($key, $data2)) {
            $state = 'removed';
        }
        if (!array_key_exists($key, $data1) && array_key_exists($key, $data2)) {
            $state = 'added';
        }
        if (array_key_exists($key, $data1) && array_key_exists($key, $data2)) {
            $oldValue = $data1[$key];
            $newValue = $data2[$key];
            if ($oldValue === $newValue) {
                $state = 'unchanged';
            } else {
                $state = 'changed';
            }
        }
        $acc[$key] = [
            'state' => $state,
            'oldValue' => $oldValue,
            'newValue' => $newValue,
        ];
        return $acc;
    }, []);

    return $diff;
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
    $result[] = '{';
    $unchanged = '    ';
    $removed = '  - ';
    $added = '  + ';

    foreach ($diff as $key => ['state' => $state, 'oldValue' => $oldValue, 'newValue' => $newValue]) {
        $oldValue = stringifyValue($oldValue);
        $newValue = stringifyValue($newValue);
        switch ($state) {
            case 'unchanged':
                $result[] = "{$unchanged}{$key}: {$oldValue}";
                break;
            case 'removed':
                $result[] = "{$removed}{$key}: {$oldValue}";
                break;
            case 'added':
                $result[] = "{$added}{$key}: {$newValue}";
                break;
            case 'changed':
                $result[] = "{$added}{$key}: {$newValue}";
                $result[] = "{$removed}{$key}: {$oldValue}";
                break;
            default:
                break;
        }
    }
    $result[] = '}';

    return implode(PHP_EOL, $result);
}

function stringifyValue($value)
{
    if (is_bool($value)) {
        $val = $value ? 'true' : 'false';
        return $val;
    }

    return $value;
}
