<?php

namespace GenDiff\Formatters\Helpers;
const END_OF_LINE = "\n";

function stringifyBoolValue(bool $value): string
{
    return $value ? 'true' : 'false';
}

function isComplexValue($value): bool
{
    return is_object($value) || is_array($value);
}
