<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class RecipeStepIngredientData extends Data
{
    public function __construct(
        public string $name,
        public ?float $quantity,
        public ?string $unit,
        public ?string $notes,
    ) {}
}
