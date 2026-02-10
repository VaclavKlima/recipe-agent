@php
    use Illuminate\Support\Arr;
@endphp

<div class="flex flex-col gap-6">
    <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-900">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                    Recipe Assistant
                </h1>
                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                    Describe what you feel like eating, and I’ll suggest recipes.
                </p>
            </div>
            <flux:badge color="{{ $isSaved ? 'green' : 'neutral' }}">
                {{ $isSaved ? 'Saved' : 'Draft' }}
            </flux:badge>
        </div>

        <div class="mt-6 grid gap-4">
            <flux:field>
                <flux:label>Recipe prompt</flux:label>
                <flux:textarea wire:model.live="prompt" rows="3" placeholder="e.g. quick chicken dinner with vegetables"></flux:textarea>
                <flux:error name="prompt" />
            </flux:field>

            <div class="flex flex-wrap gap-3">
                <flux:button variant="primary" wire:click="submitPrompt" class="data-loading:opacity-60 data-loading:pointer-events-none">
                    Suggest recipes
                </flux:button>
                @if ($suggestions)
                    <flux:button variant="ghost" wire:click="$set('suggestions', [])">
                        Clear suggestions
                    </flux:button>
                @endif
            </div>
        </div>
    </div>

    @if ($errorMessage)
        <flux:callout color="red">
            {{ $errorMessage }}
        </flux:callout>
    @endif

    @if ($suggestions)
        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-900">
            <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Suggestions</h2>
            <div class="mt-4 grid gap-4">
                @foreach ($suggestions as $index => $suggestion)
                    <div class="flex flex-col gap-2 rounded-lg border border-neutral-200 p-4 dark:border-neutral-700">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">
                                    {{ Arr::get($suggestion, 'title') }}
                                </p>
                                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                                    {{ Arr::get($suggestion, 'description') }}
                                </p>
                            </div>
                            <flux:button
                                size="sm"
                                :variant="$selectedSuggestionIndex === $index ? 'primary' : 'ghost'"
                                wire:click="selectSuggestion({{ $index }})"
                            >
                                {{ $selectedSuggestionIndex === $index ? 'Selected' : 'Select' }}
                            </flux:button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                <flux:button variant="primary" wire:click="generateRecipe" class="data-loading:opacity-60 data-loading:pointer-events-none">
                    Generate recipe
                </flux:button>
            </div>
        </div>
    @endif

    @if ($recipe)
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-900">
                <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">
                    {{ Arr::get($recipe, 'title') }}
                </h2>
                <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                    {{ Arr::get($recipe, 'description') }}
                </p>
                <p class="mt-4 text-sm font-medium text-neutral-900 dark:text-neutral-100">
                    Servings: {{ Arr::get($recipe, 'servings') }}
                </p>

                <div class="mt-5">
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Required items</h3>
                    <ul class="mt-3 space-y-2 text-sm text-neutral-600 dark:text-neutral-400">
                        @foreach (Arr::get($recipe, 'requiredItems', []) as $item)
                            <li>
                                <span class="font-medium text-neutral-900 dark:text-neutral-100">
                                    {{ Arr::get($item, 'name') }}
                                </span>
                                @if (Arr::get($item, 'quantity') || Arr::get($item, 'unit'))
                                    <span class="ml-1 text-neutral-600 dark:text-neutral-400">
                                        ({{ Arr::get($item, 'quantity') }} {{ Arr::get($item, 'unit') }})
                                    </span>
                                @endif
                                @if (Arr::get($item, 'notes'))
                                    <span class="ml-1 text-neutral-500">— {{ Arr::get($item, 'notes') }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-900">
                <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Steps</h3>
                <ol class="mt-3 space-y-4 text-sm text-neutral-600 dark:text-neutral-400">
                    @foreach (Arr::get($recipe, 'steps', []) as $step)
                        <li>
                            <div class="flex items-baseline justify-between gap-4">
                                <p class="font-medium text-neutral-900 dark:text-neutral-100">
                                    Step {{ Arr::get($step, 'stepNumber') }}:
                                </p>
                                @if (Arr::get($step, 'durationMinutes'))
                                    <span class="text-xs text-neutral-500">
                                        {{ Arr::get($step, 'durationMinutes') }} min
                                    </span>
                                @endif
                            </div>
                            <p class="mt-1">{{ Arr::get($step, 'instruction') }}</p>
                            @if (Arr::get($step, 'ingredients'))
                                <ul class="mt-2 space-y-1 text-xs text-neutral-500">
                                    @foreach (Arr::get($step, 'ingredients', []) as $ingredient)
                                        <li>
                                            {{ Arr::get($ingredient, 'name') }}
                                            @if (Arr::get($ingredient, 'quantity') || Arr::get($ingredient, 'unit'))
                                                ({{ Arr::get($ingredient, 'quantity') }} {{ Arr::get($ingredient, 'unit') }})
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>

        <div class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-900">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Adjust this recipe</h3>
            <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                Tell the assistant what to change (ingredients, diet, timing, etc.).
            </p>
            <div class="mt-4 grid gap-4">
                <flux:field>
                    <flux:label>Feedback</flux:label>
                    <flux:textarea wire:model.live="feedback" rows="3" placeholder="e.g. replace shrimp with tofu"></flux:textarea>
                    <flux:error name="feedback" />
                </flux:field>
                <div class="flex flex-wrap gap-3">
                    <flux:button variant="primary" wire:click="applyFeedback" class="data-loading:opacity-60 data-loading:pointer-events-none">
                        Update recipe
                    </flux:button>
                    <flux:button variant="ghost" wire:click="saveRecipe" class="data-loading:opacity-60 data-loading:pointer-events-none">
                        Save recipe
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
