<?php

namespace Tests\Unit\Services;

use App\Constants\CommissionRates;
use App\Services\Commissions\DefaultCommissionCalculator;
use Illuminate\Support\Collection;
use Tests\TestCase;

class DefaultCommissionCalculatorTest extends TestCase
{
    private DefaultCommissionCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new DefaultCommissionCalculator();
    }

    public function test_calculate_commission_with_valid_amount(): void
    {
        $amount = 100.0;
        $expectedCommission = $amount * CommissionRates::DEFAULT_RATE;

        $result = $this->calculator->calculateCommission($amount);

        $this->assertEquals($expectedCommission, $result);
    }

    public function test_calculate_commission_with_zero_amount(): void
    {
        $result = $this->calculator->calculateCommission(0.0);

        $this->assertEquals(0.0, $result);
    }

    public function test_calculate_commission_with_negative_amount(): void
    {
        $amount = -50.0;
        $expectedCommission = $amount * CommissionRates::DEFAULT_RATE;

        $result = $this->calculator->calculateCommission($amount);

        $this->assertEquals($expectedCommission, $result);
    }

    public function test_calculate_commission_with_decimal_amount(): void
    {
        $amount = 123.45;
        $expectedCommission = $amount * CommissionRates::DEFAULT_RATE;

        $result = $this->calculator->calculateCommission($amount);

        $this->assertEquals($expectedCommission, $result);
    }

    public function test_total_commission_with_empty_collection(): void
    {
        $sales = new Collection([]);

        $result = $this->calculator->totalCommission($sales);

        $this->assertEquals(0.0, $result);
    }

    public function test_total_commission_with_sales_having_commission_amount(): void
    {
        $sales = new Collection([
            (object) ['commission_amount' => 10.0],
            (object) ['commission_amount' => 15.5],
            (object) ['commission_amount' => 8.75],
        ]);

        $result = $this->calculator->totalCommission($sales);

        $this->assertEquals(34.25, $result);
    }

    public function test_total_commission_with_sales_without_commission_amount(): void
    {
        $sales = new Collection([
            (object) ['amount' => 100.0],
            (object) ['amount' => 200.0],
            (object) ['amount' => 50.0],
        ]);

        $expectedTotal = (100.0 + 200.0 + 50.0) * CommissionRates::DEFAULT_RATE;

        $result = $this->calculator->totalCommission($sales);

        $this->assertEquals(round($expectedTotal, 2), round($result, 2));
    }

    public function test_total_commission_with_mixed_sales(): void
    {
        $sales = new Collection([
            (object) ['commission_amount' => 10.0],
            (object) ['amount' => 100.0],
            (object) ['commission_amount' => 0, 'amount' => 200.0],
        ]);

        $expectedTotal = 10.0 + (100.0 * CommissionRates::DEFAULT_RATE) + (200.0 * CommissionRates::DEFAULT_RATE);

        $result = $this->calculator->totalCommission($sales);

        $this->assertEquals($expectedTotal, $result);
    }

    public function test_total_commission_ignores_sales_without_amount_or_commission(): void
    {
        $sales = new Collection([
            (object) ['commission_amount' => 10.0],
            (object) [],
            (object) ['amount' => 100.0],
        ]);

        $expectedTotal = 10.0 + (100.0 * CommissionRates::DEFAULT_RATE);

        $result = $this->calculator->totalCommission($sales);

        $this->assertEquals($expectedTotal, $result);
    }

    public function test_commission_rate_is_correct(): void
    {
        $amount = 1000.0;
        $result = $this->calculator->calculateCommission($amount);

        $this->assertEquals(85.0, $result);
    }
}
