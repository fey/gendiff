<?php

namespace GenDiff\Parsers;

use Symfony\Component\Yaml\Yaml;

const PARSERS = [
    'yaml' => __NAMESPACE__ . '\parseYaml',
    'yml'  => __NAMESPACE__ . '\parseYaml',
    'json' => __NAMESPACE__ . '\parseJson',
];
function parse($filePath)
{
    $fileContent = file_get_contents($filePath);
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    $parser = PARSERS[$extension];

    return $parser($fileContent);
}

function parseYaml($fileContent)
{
    return Yaml::parse($fileContent, Yaml::DUMP_OBJECT_AS_MAP);
}

function parseJson($fileContent)
{
    return json_decode($fileContent, true);
}
