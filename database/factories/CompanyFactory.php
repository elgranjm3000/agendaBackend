<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CompanyFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->company();
        
        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . $this->faker->randomNumber(4),
            'timezone' => $this->faker->randomElement(['UTC', 'America/New_York', 'America/Los_Angeles', 'Europe/London']),
            'currency' => $this->faker->randomElement(['USD', 'EUR', 'GBP', 'CAD']),
        ];
    }
}