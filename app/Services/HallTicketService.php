<?php

namespace App\Services;

class HallTicketService
{
    public function isEligible(float $attendancePercentage, bool $feesCleared): bool
    {
        return $attendancePercentage >= 75.0 && $feesCleared;
    }
}
