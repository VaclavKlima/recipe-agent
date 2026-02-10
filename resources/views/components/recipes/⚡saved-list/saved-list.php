<?php

use App\Models\Recipe;
use App\RecipeStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $search = '';

    public function toggleStar(int $recipeId): void
    {
        $recipe = Recipe::query()
            ->whereKey($recipeId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $recipe->forceFill([
            'is_starred' => ! $recipe->is_starred,
        ])->save();
    }

    #[Computed]
    public function recipes(): Collection
    {
        return Recipe::query()
            ->where('user_id', auth()->id())
            ->where('status', RecipeStatus::Saved)
            ->when($this->search !== '', function ($query): void {
                $query->where('title', 'like', '%'.$this->search.'%');
            })
            ->orderByDesc('is_starred')
            ->orderByDesc('saved_at')
            ->orderByDesc('id')
            ->get();
    }

    public function render(): View
    {
        return view('components.recipes.⚡saved-list.saved-list');
    }
};
