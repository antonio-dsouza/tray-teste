<?php

namespace Database\Factories;

use App\Models\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;

class SellerFactory extends Factory
{
    protected $model = Seller::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }

    public function withDomain(string $domain): static
    {
        return $this->state(function (array $attributes) use ($domain) {
            return [
                'email' => $this->faker->unique()->userName() . '@' . $domain,
            ];
        });
    }

    public function withNamePattern(string $pattern): static
    {
        return $this->state(function (array $attributes) use ($pattern) {
            return [
                'name' => $pattern . ' ' . $this->faker->lastName(),
            ];
        });
    }
}
