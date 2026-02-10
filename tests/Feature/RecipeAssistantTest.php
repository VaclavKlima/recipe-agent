<?php

use App\Models\User;

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
