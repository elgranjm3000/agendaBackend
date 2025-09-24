<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    public function definition(): array
    {
        $services = [
            'Haircut' => 30,
            'Hair Color' => 120,
            'Massage' => 60,
            'Facial' => 45,
            'Manicure' => 30,
            'Pedicure' => 45,
            'Eyebrow Wax' => 15,
            'Deep Cleaning Facial' => 90,
            'Swedish Massage' => 60,
            'Hot Stone Massage' => 90,
        ];

        $serviceName = fake()->randomElement(array_keys($services));
        $duration = $services[$serviceName];

        return [
            'name' => $serviceName,
            'category' => fake()->randomElement(['Beauty', 'Wellness', 'Spa', 'Hair', 'Nails']),
            'duration_minutes' => $duration,
            'price' => fake()->randomFloat(2, 25, 200),
            'is_active' => fake()->boolean(90),
            
        ];
    }
}
