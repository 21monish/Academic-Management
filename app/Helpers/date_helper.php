<?php

if (! function_exists('format_date')) {
    function format_date($date, string $format = 'd-m-Y'): string
    {
        return $date ? \Illuminate\Support\Carbon::parse($date)->format($format) : '';
    }
}
