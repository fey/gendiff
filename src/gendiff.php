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
    $diff = [];

    foreach ($data1 as $key1 => $value1) {
        if (array_key_exists($key1, $data2)) {
            $value2 = $data2[$key1];
            if ($value1 === $value2) {
                $diff[$key1] = $value1;
            } else {
                $diff["- {$key1}"] = $value1;
                $diff["+ {$key1}"] = $value2;
            }
        } else {
            $diff["- {$key1}"] = $value1;
        }
    }

    foreach ($data2 as $key2 => $value2) {
        if (array_key_exists($key2, $data1) === false) {
            $diff["+ {$key2}"] = $value2;
        }
    }


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

function stringifyDiff(array $diff)
{
    return json_encode($diff, JSON_PRETTY_PRINT);
}
