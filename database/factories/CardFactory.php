<?php

namespace Database\Factories;

use App\Enums\CardTier;
use App\Models\Card;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Card>
 */
class CardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'is_active' => $this->faker->boolean(80),
            'identifier' => $this->faker->unique()->regexify('idn-[a-z0-9]{9}-rs'),
        ];
    }

    public function standard(): static
    {
        return $this->state([
            'tier' => CardTier::STANDARD,
        ]);
    }

    public function superior(): static
    {
        return $this->state([
            'tier' => CardTier::SUPERIOR,
        ]);
    }

    public function deluxe(): static
    {
        return $this->state([
            'tier' => CardTier::DELUXE,
        ]);
    }

    public function suite(): static
    {
        return $this->state([
            'tier' => CardTier::SUITE,
        ]);
    }

    public function ambassador(): static
    {
        return $this->state([
            'tier' => CardTier::AMBASSADOR,
        ]);
    }
}
