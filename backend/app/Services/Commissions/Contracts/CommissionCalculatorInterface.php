<?php

namespace App\Services\Commissions\Contracts;

use Illuminate\Support\Collection;

interface CommissionCalculatorInterface
{
    public function totalCommission(Collection $sales): float;
    public function calculateCommission(float $amount): float;
}
