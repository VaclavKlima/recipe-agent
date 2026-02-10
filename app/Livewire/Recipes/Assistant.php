<?php

namespace App\Livewire\Recipes;

use App\Data\RecipeData;
use App\Data\RecipeSuggestionData;
use App\Models\Recipe;
use App\RecipeAiService;
use App\RecipeStatus;
use App\RecipeVersionSource;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Assistant extends Component
{
    public string $prompt = '';

    public string $feedback = '';

    public array $suggestions = [];

    public ?int $selectedSuggestionIndex = null;

    public ?array $recipe = null;

    public ?int $recipeId = null;

    public ?string $errorMessage = null;

    public bool $isSaved = false;

    public function submitPrompt(RecipeAiService $service): void
    {
        $this->reset('errorMessage');
        $this->validate([
            'prompt' => ['required', 'string', 'min:6'],
        ]);

        $recipe = $this->ensureRecipe();
        $suggestions = $service->suggestRecipes(auth()->user(), $recipe, $this->prompt);

        $this->suggestions = $suggestions->suggestions->toArray();
        $this->selectedSuggestionIndex = null;
        $this->recipe = null;
        $this->feedback = '';
        $this->isSaved = false;
    }

    public function selectSuggestion(int $index): void
    {
        $this->reset('errorMessage');

        if (! array_key_exists($index, $this->suggestions)) {
            $this->errorMessage = 'Select a valid suggestion.';

            return;
        }

        $this->selectedSuggestionIndex = $index;
    }

    public function generateRecipe(RecipeAiService $service): void
    {
        $this->reset('errorMessage');

        if ($this->selectedSuggestionIndex === null) {
            $this->errorMessage = 'Pick a suggestion first.';

            return;
        }

        $suggestion = $this->suggestions[$this->selectedSuggestionIndex] ?? null;

        if (! is_array($suggestion)) {
            $this->errorMessage = 'Select a valid suggestion.';

            return;
        }

        $recipe = $this->ensureRecipe();
        $suggestionData = RecipeSuggestionData::from($suggestion);
        $recipeData = $service->generateRecipe(auth()->user(), $recipe, $this->prompt, $suggestionData);

        $recipe->addVersion($recipeData, RecipeVersionSource::Initial);

        $this->recipe = $recipeData->toArray();
        $this->feedback = '';
        $this->isSaved = false;
    }

    public function applyFeedback(RecipeAiService $service): void
    {
        $this->reset('errorMessage');
        $this->validate([
            'feedback' => ['required', 'string', 'min:3'],
        ]);

        if (! is_array($this->recipe)) {
            $this->errorMessage = 'Generate a recipe first.';

            return;
        }

        $recipe = $this->ensureRecipe();
        $recipeData = RecipeData::from($this->recipe);
        $updatedRecipe = $service->refineRecipe(auth()->user(), $recipe, $recipeData, $this->feedback);

        $recipe->addVersion($updatedRecipe, RecipeVersionSource::Refinement);

        $this->recipe = $updatedRecipe->toArray();
        $this->feedback = '';
        $this->isSaved = false;
    }

    public function saveRecipe(): void
    {
        $this->reset('errorMessage');

        if (! is_array($this->recipe)) {
            $this->errorMessage = 'Generate a recipe before saving.';

            return;
        }

        $recipe = $this->ensureRecipe();
        $recipe->forceFill([
            'status' => RecipeStatus::Saved,
            'saved_at' => now(),
        ])->save();

        $this->isSaved = true;
    }

    public function render(): View
    {
        return view('livewire.recipes.assistant')
            ->layout('layouts.app', ['title' => 'Recipe Assistant']);
    }

    private function ensureRecipe(): Recipe
    {
        $userId = auth()->id();

        if ($this->recipeId) {
            return Recipe::query()
                ->whereKey($this->recipeId)
                ->where('user_id', $userId)
                ->firstOrFail();
        }

        $recipe = Recipe::create([
            'user_id' => $userId,
            'status' => RecipeStatus::Draft,
        ]);

        $this->recipeId = $recipe->id;

        return $recipe;
    }
}
