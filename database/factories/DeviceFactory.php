<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Device>
 */
class DeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'identifier' => $this->faker->unique()->regexify('dev-[a-z0-9]{9}-rs'),
            'name' => $this->faker->unique()->macAddress(),
        ];
    }
}
