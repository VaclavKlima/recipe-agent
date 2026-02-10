<div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-900">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                Saved recipes
            </h2>
            <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                Search and revisit your favorite recipes.
            </p>
        </div>
    </div>

    <div class="mt-4">
        <flux:field>
            <flux:label>Search by title</flux:label>
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="e.g. Mushroom risotto"
            />
        </flux:field>
    </div>

    @if ($this->recipes->isEmpty())
        <p class="mt-6 text-sm text-neutral-500 dark:text-neutral-400">
            No saved recipes yet.
        </p>
    @else
        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($this->recipes as $recipe)
                <flux:card wire:key="saved-recipe-{{ $recipe->id }}" class="flex h-full flex-col gap-4 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="truncate text-sm font-semibold text-neutral-900 dark:text-neutral-100">
                                {{ $recipe->title ?: 'Untitled recipe' }}
                            </h3>
                            @if ($recipe->description)
                                <p class="mt-1 text-xs text-neutral-600 dark:text-neutral-400">
                                    {{ $recipe->description }}
                                </p>
                            @endif
                        </div>
                        <flux:button
                            size="sm"
                            variant="{{ $recipe->is_starred ? 'primary' : 'ghost' }}"
                            wire:click="toggleStar({{ $recipe->id }})"
                        >
                            {{ $recipe->is_starred ? 'Starred' : 'Star' }}
                        </flux:button>
                    </div>

                    <div class="mt-auto flex flex-wrap gap-2 text-xs text-neutral-500 dark:text-neutral-400">
                        @if ($recipe->servings)
                            <span>Servings: {{ $recipe->servings }}</span>
                        @endif
                        @if ($recipe->saved_at)
                            <span>Saved {{ $recipe->saved_at->format('M j, Y') }}</span>
                        @endif
                    </div>
                </flux:card>
            @endforeach
        </div>
    @endif
</div>
