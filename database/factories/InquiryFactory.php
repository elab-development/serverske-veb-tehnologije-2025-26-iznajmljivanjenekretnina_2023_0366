<?php

namespace Database\Factories;

use App\Models\Inquiry;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inquiry>
 */
class InquiryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'property_id' => Property::factory(),
            'message' => fake()->optional()->paragraph(),
            'phone' => fake()->optional()->phoneNumber(),
            'preferred_date' => fake()->optional()->dateTimeBetween('tomorrow', '+1 month'),
            'preferred_time' => fake()->optional()->time('H:i:s'),
            'status' => Inquiry::STATUS_NEW,
            'admin_note' => null,
        ];
    }

    /**
     * Indicate that the inquiry has been contacted.
     */
    public function contacted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Inquiry::STATUS_CONTACTED,
        ]);
    }

    /**
     * Indicate that the inquiry has been scheduled.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Inquiry::STATUS_SCHEDULED,
        ]);
    }

    /**
     * Indicate that the inquiry has been closed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Inquiry::STATUS_CLOSED,
        ]);
    }
}
