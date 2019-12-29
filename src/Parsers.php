<?php

namespace fey\GenDiff\Parsers;

use Symfony\Component\Yaml\Yaml;

function parse($data, $type)
{
    $parsers = [
        'yaml' => fn($content) => parseYaml($content),
        'yml'  => fn($content) => parseYaml($content),
        'json' => fn($content) => parseJson($content),
    ];
    return $parsers[$type]($data);
}

function parseYaml($fileContent): array
{
    return Yaml::parse($fileContent, Yaml::DUMP_OBJECT_AS_MAP);
}

function parseJson($fileContent): array
{
    return json_decode($fileContent, true);
}
