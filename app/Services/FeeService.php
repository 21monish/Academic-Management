<?php

namespace App\Services;

class FeeService
{
    public function netPayable(float $gross, float $concession = 0.0, float $scholarship = 0.0): float
    {
        return max(0.0, round($gross - $concession - $scholarship, 2));
    }
}
