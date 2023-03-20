<?php

declare(strict_types=1);

namespace Liliana\Setup;

class Helper
{
    public const PreventMerging = '_prevent_merging';

    /**
     * Merges dataset. Left has higher priority than right one.
     * @see https://github.com/nette/schema/blob/master/src/Schema/Helpers.php
     */
    public static function merge(mixed $value, mixed $base): mixed
    {
        if (is_array($value) && isset($value[self::PreventMerging])) {
            unset($value[self::PreventMerging]);
            return $value;
        }

        if (is_array($value) && is_array($base)) {
            $index = 0;
            foreach ($value as $key => $val) {
                if ($key === $index) {
                    $base[] = $val;
                    $index++;
                } else {
                    $base[$key] = static::merge($val, $base[$key] ?? null);
                }
            }

            return $base;

        } elseif ($value === null && is_array($base)) {
            return $base;

        } else {
            return $value;
        }
    }

    public static function match(string $pattern, string $subject): ?array
    {
        preg_match($pattern, $subject, $matches);

        // $subject is not a match
        if (!$matches || count($matches) < 1) {
            return null;
        }

        return $matches;
    }

    public static function objectExists(string $identifier): bool
    {
        return class_exists($identifier)
            || interface_exists($identifier)
            || trait_exists($identifier)
            || enum_exists($identifier);
    }

}