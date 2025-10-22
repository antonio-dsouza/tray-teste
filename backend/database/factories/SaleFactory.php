<?php

namespace Database\Factories;

use App\Constants\CommissionRates;
use App\Models\Sale;
use App\Models\Seller;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        $amount = $this->faker->randomFloat(2, 10, 1000);

        return [
            'seller_id' => Seller::factory(),
            'amount' => $amount,
            'commission_amount' => $amount * CommissionRates::DEFAULT_RATE,
            'sold_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function withAmount(float $amount): static
    {
        return $this->state(function (array $attributes) use ($amount) {
            return [
                'amount' => $amount,
                'commission_amount' => $amount * CommissionRates::DEFAULT_RATE,
            ];
        });
    }

    public function today(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'sold_at' => Carbon::today()->addHours($this->faker->numberBetween(0, 23)),
            ];
        });
    }

    public function onDate(string $date): static
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'sold_at' => Carbon::parse($date)->addHours($this->faker->numberBetween(0, 23)),
            ];
        });
    }

    public function highValue(): static
    {
        return $this->state(function (array $attributes) {
            $amount = $this->faker->randomFloat(2, 1000, 10000);
            return [
                'amount' => $amount,
                'commission_amount' => $amount * CommissionRates::DEFAULT_RATE,
            ];
        });
    }

    public function lowValue(): static
    {
        return $this->state(function (array $attributes) {
            $amount = $this->faker->randomFloat(2, 1, 50);
            return [
                'amount' => $amount,
                'commission_amount' => $amount * CommissionRates::DEFAULT_RATE,
            ];
        });
    }
}
