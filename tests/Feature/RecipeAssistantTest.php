<?php

use App\Livewire\Recipes\Assistant;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to login when visiting the recipe assistant', function () {
    $response = $this->get(route('recipes.assistant'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can view the recipe assistant page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('recipes.assistant'))
        ->assertOk()
        ->assertSee('Recipe Assistant');
});

test('users can update recipe assistant language preference', function () {
    $user = User::factory()->create(['locale' => 'en']);

    $this->actingAs($user);

    Livewire::test(Assistant::class)
        ->set('locale', 'fr')
        ->call('updateLocale')
        ->assertHasNoErrors();

    expect($user->refresh()->locale)->toBe('fr');
});
