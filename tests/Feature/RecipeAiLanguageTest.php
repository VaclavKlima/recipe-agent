<?php

use App\Models\Recipe;
use App\Models\User;
use App\RecipeAiService;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\StructuredResponseFake;

test('recipe prompts stay in the selected language with locale-based weight units', function (
    string $locale,
    string $language,
    string $unitSystem
) {
    $user = User::factory()->create(['locale' => $locale]);
    $recipe = Recipe::factory()->create(['user_id' => $user->id]);

    $fakeResponse = StructuredResponseFake::make()
        ->withText(json_encode([
            'suggestions' => [
                ['title' => 'Test', 'description' => 'Desc'],
            ],
        ], JSON_THROW_ON_ERROR))
        ->withStructured([
            'suggestions' => [
                ['title' => 'Test', 'description' => 'Desc'],
            ],
        ]);

    $fake = Prism::fake([$fakeResponse]);

    $service = app(RecipeAiService::class);
    $service->suggestRecipes($user, $recipe, 'Give me ideas for dinner');

    $expectedUnitsLine = $unitSystem === 'imperial'
        ? 'Use imperial weight units (oz, lb) for ingredient amounts.'
        : 'Use metric weight units (g, kg) for ingredient amounts.';

    $fake->assertRequest(function (array $requests) use ($language, $expectedUnitsLine) {
        $systemPrompts = $requests[0]->systemPrompts();
        $combined = collect($systemPrompts)
            ->map(fn ($prompt) => $prompt->content)
            ->implode(' ');

        expect($combined)->toContain("Respond only in {$language}.")
            ->and($combined)->toContain('Do not switch languages')
            ->and($combined)->toContain($expectedUnitsLine);
    });
})->with('recipe_language_units');

dataset('recipe_language_units', [
    'english_imperial' => ['en', 'English', 'imperial'],
    'czech_metric' => ['cs', 'Czech', 'metric'],
    'spanish_metric' => ['es', 'Spanish', 'metric'],
]);
