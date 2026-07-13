<?php

namespace App\Services;

class AttendanceService
{
    public function percentage(int $present, int $total): float
    {
        return $total > 0 ? round(($present / $total) * 100, 2) : 0.0;
    }
}
