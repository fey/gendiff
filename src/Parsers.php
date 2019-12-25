<?php

namespace GenDiff\Parsers;

use Symfony\Component\Yaml\Yaml;

function parse($fileContent, $parser)
{
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
