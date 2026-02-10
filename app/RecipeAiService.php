<?php

namespace App;

use App\Data\RecipeData;
use App\Data\RecipeSuggestionData;
use App\Data\RecipeSuggestionListData;
use App\Models\Recipe;
use App\Models\RecipePromptLog;
use App\Models\User;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\NumberSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use RuntimeException;

class RecipeAiService
{
    private const PROVIDER = Provider::Gemini;

    private const MODEL = 'gemini-3-flash-preview';

    public function suggestRecipes(User $user, Recipe $recipe, string $prompt): RecipeSuggestionListData
    {
        $schema = $this->suggestionsSchema();
        $languageContext = $this->languageContext($user);
        $systemPrompt = $this->suggestionsSystemPrompt($languageContext);

        $response = Prism::structured()
            ->using(self::PROVIDER, self::MODEL)
            ->withSchema($schema)
            ->withSystemPrompt($systemPrompt)
            ->withPrompt($prompt)
            ->asStructured();

        $structured = $this->requireStructured($response->structured, 'suggestions');
        $suggestions = RecipeSuggestionListData::from($structured);

        $this->logPrompt($user, $recipe, RecipePromptType::Suggestions, $prompt, $response, [
            'system_prompt' => $systemPrompt,
            'schema' => 'recipe_suggestions',
            'language' => $languageContext,
        ]);

        return $suggestions;
    }

    public function generateRecipe(
        User $user,
        Recipe $recipe,
        string $prompt,
        RecipeSuggestionData $suggestion
    ): RecipeData {
        $schema = $this->recipeSchema();
        $languageContext = $this->languageContext($user);
        $systemPrompt = $this->recipeSystemPrompt($languageContext);
        $fullPrompt = $this->combinePromptWithSuggestion($prompt, $suggestion);

        $response = Prism::structured()
            ->using(self::PROVIDER, self::MODEL)
            ->withSchema($schema)
            ->withSystemPrompt($systemPrompt)
            ->withPrompt($fullPrompt)
            ->asStructured();

        $structured = $this->requireStructured($response->structured, 'recipe');
        $recipeData = RecipeData::from($structured);

        $this->logPrompt($user, $recipe, RecipePromptType::Structured, $fullPrompt, $response, [
            'system_prompt' => $systemPrompt,
            'schema' => 'recipe',
            'language' => $languageContext,
        ]);

        return $recipeData;
    }

    public function refineRecipe(
        User $user,
        Recipe $recipe,
        RecipeData $currentRecipe,
        string $feedback
    ): RecipeData {
        $schema = $this->recipeSchema();
        $languageContext = $this->languageContext($user);
        $systemPrompt = $this->refineSystemPrompt($languageContext);
        $fullPrompt = $this->combineRecipeWithFeedback($currentRecipe, $feedback);

        $response = Prism::structured()
            ->using(self::PROVIDER, self::MODEL)
            ->withSchema($schema)
            ->withSystemPrompt($systemPrompt)
            ->withPrompt($fullPrompt)
            ->asStructured();

        $structured = $this->requireStructured($response->structured, 'recipe');
        $recipeData = RecipeData::from($structured);

        $this->logPrompt($user, $recipe, RecipePromptType::Refinement, $fullPrompt, $response, [
            'system_prompt' => $systemPrompt,
            'schema' => 'recipe',
            'language' => $languageContext,
        ]);

        return $recipeData;
    }

    private function requireStructured(?array $structured, string $context): array
    {
        if (! is_array($structured)) {
            throw new RuntimeException("Missing structured {$context} response.");
        }

        return $structured;
    }

    private function logPrompt(
        User $user,
        Recipe $recipe,
        RecipePromptType $type,
        string $prompt,
        object $response,
        array $request
    ): void {
        $usage = $response->usage ?? null;
        $finishReason = $response->finishReason ?? null;

        RecipePromptLog::create([
            'user_id' => $user->id,
            'recipe_id' => $recipe->id,
            'type' => $type,
            'prompt' => $prompt,
            'response' => [
                'structured' => $response->structured,
                'text' => $response->text,
            ],
            'provider' => 'gemini',
            'model' => self::MODEL,
            'request' => $request,
            'meta' => [
                'finish_reason' => $finishReason?->name,
                'usage' => $usage ? [
                    'prompt_tokens' => $usage->promptTokens ?? null,
                    'completion_tokens' => $usage->completionTokens ?? null,
                ] : null,
            ],
        ]);
    }

    private function suggestionsSchema(): ObjectSchema
    {
        return new ObjectSchema(
            name: 'recipe_suggestions',
            description: 'Short recipe ideas based on the user prompt',
            properties: [
                new ArraySchema(
                    name: 'suggestions',
                    description: 'List of recipe suggestions',
                    items: new ObjectSchema(
                        name: 'suggestion',
                        description: 'A single recipe suggestion',
                        properties: [
                            new StringSchema('title', 'Recipe title'),
                            new StringSchema('description', 'Short description'),
                        ],
                        requiredFields: ['title', 'description']
                    )
                ),
            ],
            requiredFields: ['suggestions']
        );
    }

    private function recipeSchema(): ObjectSchema
    {
        return new ObjectSchema(
            name: 'recipe',
            description: 'Structured recipe details',
            properties: [
                new StringSchema('title', 'Recipe title'),
                new StringSchema('description', 'Short description of the meal'),
                new NumberSchema('servings', 'Number of servings'),
                new ArraySchema(
                    name: 'requiredItems',
                    description: 'All needed ingredients and items for the meal',
                    items: new ObjectSchema(
                        name: 'required_item',
                        description: 'Ingredient or needed item',
                        properties: [
                            new StringSchema('name', 'Item name'),
                            new NumberSchema('quantity', 'Amount needed', nullable: true),
                            new StringSchema('unit', 'Unit of measure', nullable: true),
                            new StringSchema('notes', 'Additional notes', nullable: true),
                        ],
                        requiredFields: ['name', 'quantity', 'unit', 'notes']
                    )
                ),
                new ArraySchema(
                    name: 'steps',
                    description: 'Step-by-step instructions',
                    items: new ObjectSchema(
                        name: 'step',
                        description: 'A single cooking step',
                        properties: [
                            new NumberSchema('stepNumber', 'Step number'),
                            new StringSchema('instruction', 'Instruction text'),
                            new NumberSchema('durationMinutes', 'Estimated minutes', nullable: true),
                            new ArraySchema(
                                name: 'ingredients',
                                description: 'Ingredients used in this step',
                                items: new ObjectSchema(
                                    name: 'step_ingredient',
                                    description: 'Ingredient used in the step',
                                    properties: [
                                        new StringSchema('name', 'Ingredient name'),
                                        new NumberSchema('quantity', 'Amount used', nullable: true),
                                        new StringSchema('unit', 'Unit of measure', nullable: true),
                                        new StringSchema('notes', 'Additional notes', nullable: true),
                                    ],
                                    requiredFields: ['name', 'quantity', 'unit', 'notes']
                                )
                            ),
                        ],
                        requiredFields: ['stepNumber', 'instruction', 'durationMinutes', 'ingredients']
                    )
                ),
            ],
            requiredFields: ['title', 'description', 'servings', 'requiredItems', 'steps']
        );
    }

    /**
     * @param  array{locale: string, language: string, unit_system: string}  $languageContext
     */
    private function suggestionsSystemPrompt(array $languageContext): string
    {
        $languageDirective = $this->languageDirective($languageContext);

        return 'You are a helpful chef assistant. Suggest 3 to 5 recipe ideas with short descriptions. Keep them concise. '
            .$languageDirective;
    }

    /**
     * @param  array{locale: string, language: string, unit_system: string}  $languageContext
     */
    private function recipeSystemPrompt(array $languageContext): string
    {
        $languageDirective = $this->languageDirective($languageContext);

        return 'You are a helpful chef assistant. Produce a structured recipe with ingredients and steps. Steps must include exact ingredient amounts and timing. '
            .$languageDirective;
    }

    /**
     * @param  array{locale: string, language: string, unit_system: string}  $languageContext
     */
    private function refineSystemPrompt(array $languageContext): string
    {
        $languageDirective = $this->languageDirective($languageContext);

        return 'You are a helpful chef assistant. Update the recipe according to the feedback while keeping it structured. '
            .$languageDirective;
    }

    private function combinePromptWithSuggestion(string $prompt, RecipeSuggestionData $suggestion): string
    {
        return "User prompt: {$prompt}\n\nSelected suggestion: {$suggestion->title} - {$suggestion->description}\n\nGenerate the full recipe.";
    }

    private function combineRecipeWithFeedback(RecipeData $recipe, string $feedback): string
    {
        $recipeJson = json_encode($recipe->toArray(), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        return "Current recipe (JSON):\n{$recipeJson}\n\nUser feedback: {$feedback}\n\nUpdate the recipe.";
    }

    /**
     * @return array{locale: string, language: string, unit_system: string}
     */
    private function languageContext(User $user): array
    {
        $locale = $this->normalizeLocale($user->locale ?? config('app.locale'));
        $locales = config('app.locales', []);
        $language = $locales[$locale] ?? $locale;
        $unitSystem = $this->unitSystemForLocale($locale);

        return [
            'locale' => $locale,
            'language' => $language,
            'unit_system' => $unitSystem,
        ];
    }

    private function normalizeLocale(?string $locale): string
    {
        $availableLocales = array_keys(config('app.locales', []));
        $defaultLocale = config('app.locale');
        $locale = $locale ?: $defaultLocale;

        if (! in_array($locale, $availableLocales, true)) {
            return $defaultLocale;
        }

        return $locale;
    }

    private function unitSystemForLocale(string $locale): string
    {
        $imperialLocales = ['en'];

        return in_array($locale, $imperialLocales, true) ? 'imperial' : 'metric';
    }

    /**
     * @param  array{locale: string, language: string, unit_system: string}  $languageContext
     */
    private function languageDirective(array $languageContext): string
    {
        $unitsInstruction = $languageContext['unit_system'] === 'imperial'
            ? 'Use imperial weight units (oz, lb) for ingredient amounts.'
            : 'Use metric weight units (g, kg) for ingredient amounts.';

        return "Respond only in {$languageContext['language']}. Do not switch languages, even if the user writes in another language. {$unitsInstruction}";
    }
}
