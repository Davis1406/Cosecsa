<?php

namespace App\Support;

class Emails
{
    /**
     * Escaped HTML for a cell that holds one or more email addresses: a second address
     * (separated by "/", ",", ";" or whitespace) goes on its own line under the first.
     */
    public static function lines(?string $value, string $empty = ''): string
    {
        $parts = preg_split('/[\s,;\/]+/', trim((string) $value), -1, PREG_SPLIT_NO_EMPTY);

        return $parts ? implode('<br>', array_map(fn ($e) => e($e), $parts)) : e($empty);
    }
}
