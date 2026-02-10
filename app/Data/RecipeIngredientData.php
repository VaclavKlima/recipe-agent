<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class RecipeIngredientData extends Data
{
    public function __construct(
        public string $name,
        public ?float $quantity,
        public ?string $unit,
        public ?string $notes,
    ) {}
}
