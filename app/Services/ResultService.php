<?php

namespace App\Services;

class ResultService
{
    public function gradePoint(float $marks): float
    {
        return match (true) {
            $marks >= 85 => 10.0,
            $marks >= 75 => 9.0,
            $marks >= 65 => 8.0,
            $marks >= 55 => 7.0,
            $marks >= 45 => 6.0,
            $marks >= 35 => 5.0,
            default => 0.0,
        };
    }
}
