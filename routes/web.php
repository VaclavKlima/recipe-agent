<?php

use App\Livewire\Recipes\Assistant as RecipeAssistant;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/', 'dashboard')
        ->name('dashboard');

    Route::redirect('dashboard', '/');

    Route::livewire('recipes/assistant', RecipeAssistant::class)
        ->name('recipes.assistant');
});

require __DIR__.'/settings.php';
