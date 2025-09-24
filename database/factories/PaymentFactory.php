<?php

namespace Database\Factories;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'amount' => fake()->randomFloat(2, 10, 300),
            'method' => fake()->randomElement(['cash', 'card', 'online']),
            'status' => fake()->randomElement(['pending', 'paid', 'refunded']),
            'transaction_reference' => fake()->optional(0.7)->regexify('[A-Z0-9]{10}'),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'transaction_reference' => fake()->regexify('[A-Z0-9]{10}'),
        ]);
    }
}