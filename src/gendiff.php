<?php

namespace fey\GenDiff;

function genDiff(string $filePath1, string $filePath2): string
{
    $fileContent1 = file_get_contents($filePath1);
    $fileContent2 = file_get_contents($filePath2);

    $data1 = parseJson($fileContent1);
    $data2 = parseJson($fileContent2);

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
                $diff["- {$key1}"]= $value1;
                $diff["+ {$key1}"]= $value2;
            }
        } else {
            $diff["- {$key1}"]= $value1;
        }
    }

    foreach ($data2 as $key2 => $value2) {
        if (array_key_exists($key2, $data1) === false) {
            $diff["+ {$key2}"]= $value2;
        }
    }


    return $diff;
}


function parseJson(string $data)
{
    return json_decode($data, true);
}

function stringifyDiff(array $diff)
{
    return json_encode($diff, JSON_PRETTY_PRINT);
}
