<?php

namespace App\Services\Commissions;

use App\Constants\CommissionRates;
use App\Services\Commissions\Contracts\CommissionCalculatorInterface;
use Illuminate\Support\Collection;

class DefaultCommissionCalculator implements CommissionCalculatorInterface
{
    public function totalCommission(Collection $sales): float
    {
        if ($sales->isEmpty()) {
            return 0.0;
        }

        return $sales->sum(function ($sale) {
            if (isset($sale->commission_amount) && $sale->commission_amount > 0) {
                return $sale->commission_amount;
            }

            return $this->calculateCommission($sale->amount ?? 0);
        });
    }

    public function calculateCommission(float $amount): float
    {
        return $amount * CommissionRates::DEFAULT_RATE;
    }
}
