<?php

use App\Livewire\Settings\Appearance;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

test('user can update language preference', function () {
    $user = User::factory()->create(['locale' => 'en']);

    $this->actingAs($user);

    Livewire::test(Appearance::class)
        ->set('locale', 'es')
        ->call('updateLocale')
        ->assertHasNoErrors();

    expect($user->refresh()->locale)->toBe('es');
});

test('middleware sets locale for authenticated users', function () {
    Route::middleware('web')->get('/__test-locale', fn () => app()->getLocale());

    $user = User::factory()->create(['locale' => 'fr']);

    $this->actingAs($user)
        ->get('/__test-locale')
        ->assertOk()
        ->assertSee('fr');
});
