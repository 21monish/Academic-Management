<?php

if (! function_exists('attendance_percentage')) {
    function attendance_percentage(int $present, int $total): float
    {
        return $total > 0 ? round(($present / $total) * 100, 2) : 0.0;
    }
}
