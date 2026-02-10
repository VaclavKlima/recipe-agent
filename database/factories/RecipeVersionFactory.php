<?php

namespace Database\Factories;

use App\Models\Recipe;
use App\RecipeVersionSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecipeVersion>
 */
class RecipeVersionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'recipe_id' => Recipe::factory(),
            'version_number' => 1,
            'source' => RecipeVersionSource::Initial,
            'structured_recipe' => [
                'title' => $this->faker->words(3, true),
                'description' => $this->faker->sentence(),
                'servings' => $this->faker->numberBetween(1, 6),
                'requiredItems' => [
                    [
                        'name' => 'Olive oil',
                        'quantity' => 1,
                        'unit' => 'tbsp',
                        'notes' => null,
                    ],
                ],
                'steps' => [
                    [
                        'stepNumber' => 1,
                        'instruction' => 'Combine ingredients and cook.',
                        'durationMinutes' => 10,
                        'ingredients' => [
                            [
                                'name' => 'Olive oil',
                                'quantity' => 1,
                                'unit' => 'tbsp',
                                'notes' => null,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
