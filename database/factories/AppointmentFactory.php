<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        $startTime = fake()->dateTimeBetween('now', '+2 months');
        $service = Service::factory()->create();
        $endTime = (clone $startTime)->modify("+{$service->duration_minutes} minutes");

        return [
            'client_id' => Client::factory(),
            'service_id' => $service->id,
            'user_id' => User::factory(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => fake()->randomElement(['scheduled', 'completed', 'cancelled', 'no_show']),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
            'start_time' => fake()->dateTimeBetween('+1 day', '+2 months'),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'start_time' => fake()->dateTimeBetween('-2 months', '-1 day'),
        ]);
    }
}
