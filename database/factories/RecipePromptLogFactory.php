<?php

namespace Database\Factories;

use App\Models\Recipe;
use App\Models\User;
use App\RecipePromptType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecipePromptLog>
 */
class RecipePromptLogFactory extends Factory
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
            'recipe_id' => Recipe::factory(),
            'type' => RecipePromptType::Suggestions,
            'prompt' => $this->faker->sentence(),
            'response' => [
                'structured' => [
                    'suggestions' => [
                        [
                            'title' => $this->faker->words(3, true),
                            'description' => $this->faker->sentence(),
                        ],
                    ],
                ],
            ],
            'provider' => 'gemini',
            'model' => 'gemini-3-flash-preview',
            'request' => [
                'system_prompt' => 'Test system prompt',
            ],
            'meta' => [
                'finish_reason' => 'stop',
            ],
        ];
    }
}
