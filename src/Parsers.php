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

function parseYaml($data)
{
    return Yaml::parse($data, Yaml::PARSE_OBJECT_FOR_MAP);
}

function parseJson($data)
{
    return json_decode($data);
}
