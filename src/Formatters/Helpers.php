<?php

namespace GenDiff\Formatters\Helpers;

const END_OF_LINE = "\n";

function stringifyBoolValue(bool $value): string
{
    return $value ? 'true' : 'false';
}
