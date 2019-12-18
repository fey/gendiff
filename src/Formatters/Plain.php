<?php

namespace GenDiff\Formatters\Plain;

use function GenDiff\Formatters\Helpers\stringifyIfBoolValue;

use const GenDiff\CHANGED;
use const GenDiff\UNCHANGED;
use const GenDiff\REMOVED;
use const GenDiff\ADDED;

function format(array $diff): string
{
    $renderer = function ($nodes, $acc) use (&$renderer) {
        return array_reduce($nodes, function ($acc, $node) use ($renderer) {
            [
                'state'    => $state,
                'path'     => $path,
                'newValue' => $newValue,
                'oldValue' => $oldValue,
                'key'      => $key,
                'children' => $children
            ] = $node;
            $fullPath = implode('.', $path);
            switch ($state) {
                case CHANGED:
                    $acc[] = sprintf(
                        "Property '%s' was changed. From '%s' to '%s'",
                        $fullPath,
                        stringifyIfBoolValue($oldValue),
                        stringifyIfBoolValue($newValue)
                    );
                    break;
                case UNCHANGED:
                    if ($children) {
                        return $renderer($children, $acc);
                    }
                    break;
                case ADDED:
                    $acc[] = sprintf(
                        "Property '%s' was added with value: '%s'",
                        $fullPath,
                        $children ? 'complex value' : stringifyIfBoolValue($newValue)
                    );
                    break;
                case REMOVED:
                    $acc[] = sprintf(
                        "Property '%s' was removed",
                        $fullPath,
                    );
                    break;
                default:
                    break;
            }

            return $acc;
        }, $acc);
    };

    return implode(PHP_EOL, $renderer($diff, [])) . PHP_EOL;
}
