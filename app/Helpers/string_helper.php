<?php

if (! function_exists('truncate_text')) {
    function truncate_text(string $value, int $limit = 80): string
    {
        return \Illuminate\Support\Str::limit($value, $limit);
    }
}
