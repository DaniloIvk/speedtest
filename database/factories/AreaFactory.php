<?php

namespace Database\Factories;

use App\Models\Area;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Area>
 */
class AreaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'path' => '',
            'name' => $this->faker->unique()->buildingNumber(),
        ];
    }
}
