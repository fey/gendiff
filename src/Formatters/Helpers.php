<?php

namespace fey\GenDiff\Formatters\Helpers;

function stringifyIfBoolValue($value)
{
    if (is_bool($value)) {
        $val = $value ? 'true' : 'false';
        return $val;
    }

    return $value;
}

function isComplexValue($value): bool
{
    return is_object($value) || is_array($value);
}
