<?php

namespace App\Utils;

class Sql
{
    public static function toOneline(string $input): string
    {
        return rtrim(trim(preg_replace('!\s+!', ' ', str_replace(["\n", "\r"], ' ', $input))), ';');
    }

    public static function escape(string $input): string
    {
        $replacements = [
            '?|' => '??|',
            # TODO: more
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $input);
    }
}
