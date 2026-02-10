<?php

use App\Models\Recipe;
use App\Models\User;
use App\RecipeStatus;
use Livewire\Livewire;

test('users can search saved recipes by title', function () {
    $user = User::factory()->create();

    Recipe::factory()->create([
        'user_id' => $user->id,
        'status' => RecipeStatus::Saved,
        'title' => 'Apple Pie',
        'saved_at' => now(),
    ]);

    Recipe::factory()->create([
        'user_id' => $user->id,
        'status' => RecipeStatus::Saved,
        'title' => 'Banana Bread',
        'saved_at' => now(),
    ]);

    $this->actingAs($user);

    Livewire::test('recipes.saved-list')
        ->set('search', 'Apple')
        ->assertSee('Apple Pie')
        ->assertDontSee('Banana Bread');
});

test('starred recipes appear first', function () {
    $user = User::factory()->create();

    Recipe::factory()->create([
        'user_id' => $user->id,
        'status' => RecipeStatus::Saved,
        'title' => 'Newest Regular',
        'saved_at' => now(),
        'is_starred' => false,
    ]);

    Recipe::factory()->create([
        'user_id' => $user->id,
        'status' => RecipeStatus::Saved,
        'title' => 'Starred Favorite',
        'saved_at' => now()->subDay(),
        'is_starred' => true,
    ]);

    $this->actingAs($user);

    Livewire::test('recipes.saved-list')
        ->assertSeeInOrder(['Starred Favorite', 'Newest Regular']);
});
