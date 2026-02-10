<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class RecipeSuggestionData extends Data
{
    public function __construct(
        public string $title,
        public string $description,
    ) {}
}
