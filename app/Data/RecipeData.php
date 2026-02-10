<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class RecipeData extends Data
{
    public function __construct(
        public string $title,
        public string $description,
        public int $servings,
        #[DataCollectionOf(RecipeIngredientData::class)]
        public DataCollection $requiredItems,
        #[DataCollectionOf(RecipeStepData::class)]
        public DataCollection $steps,
    ) {}
}
