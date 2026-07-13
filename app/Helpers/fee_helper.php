<?php

if (! function_exists('net_payable')) {
    function net_payable(float $gross, float $concession = 0.0, float $scholarship = 0.0): float
    {
        return max(0.0, round($gross - $concession - $scholarship, 2));
    }
}
