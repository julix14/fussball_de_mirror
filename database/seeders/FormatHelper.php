<?php

namespace Database\Seeders;

use Illuminate\Support\Str;

class FormatHelper
{
    public static function formatKey($key): string
    {
        if (Str::startsWith($key, '_')) {
            return str_replace('_', '', $key);
        }
        return '_'.$key;
    }

}
