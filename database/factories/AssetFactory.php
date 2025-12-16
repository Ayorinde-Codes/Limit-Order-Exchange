<?php

namespace Database\Factories;

use App\Enums\Symbol;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Asset>
 */
class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'symbol' => $this->faker->randomElement([Symbol::BTC, Symbol::ETH]),
            'amount' => $this->faker->randomFloat(8, 0.1, 2.0),
            'locked_amount' => 0,
        ];
    }

    public function btc(): static
    {
        return $this->state(fn (array $attributes) => [
            'symbol' => Symbol::BTC,
        ]);
    }

    public function eth(): static
    {
        return $this->state(fn (array $attributes) => [
            'symbol' => Symbol::ETH,
        ]);
    }

    public function withAmount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
        ]);
    }
}
