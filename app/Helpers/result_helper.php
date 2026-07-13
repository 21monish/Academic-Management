<?php

if (! function_exists('grade_point')) {
    function grade_point(float $marks): float
    {
        return app(\App\Services\ResultService::class)->gradePoint($marks);
    }
}
