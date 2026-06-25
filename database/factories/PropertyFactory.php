<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement([
            Property::STATUS_DRAFT,
            Property::STATUS_ACTIVE,
            Property::STATUS_ARCHIVED,
        ]);

        return [
            'category_id' => Category::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraphs(3, true),
            'price' => fake()->randomFloat(2, 30000, 700000),
            'city' => fake()->city(),
            'address' => fake()->streetAddress(),
            'area' => fake()->randomFloat(2, 20, 400),
            'rooms' => fake()->optional()->randomFloat(1, 1, 6),
            'bathrooms' => fake()->optional()->numberBetween(1, 4),
            'floor' => fake()->optional()->randomElement(['ground', '1', '2', '3', '4', '5']),
            'total_floors' => fake()->optional()->numberBetween(1, 12),
            'year_built' => fake()->optional()->numberBetween(1950, now()->year),
            'listing_type' => fake()->randomElement([
                Property::LISTING_TYPE_SALE,
                Property::LISTING_TYPE_RENT,
            ]),
            'status' => $status,
            'is_featured' => fake()->boolean(20),
            'published_at' => $status === Property::STATUS_ACTIVE ? fake()->dateTimeBetween('-1 year') : null,
        ];
    }

    /**
     * Indicate that the property is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Property::STATUS_ACTIVE,
            'published_at' => now(),
        ]);
    }

    /**
     * Indicate that the property is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Property::STATUS_DRAFT,
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the property is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
