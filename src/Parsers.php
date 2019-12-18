<?php

namespace GenDiff\Parsers;

use Symfony\Component\Yaml\Yaml;

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
