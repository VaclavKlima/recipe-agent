<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class RecipeStepData extends Data
{
    public function __construct(
        public int $stepNumber,
        public string $instruction,
        public ?int $durationMinutes,
        #[DataCollectionOf(RecipeStepIngredientData::class)]
        public DataCollection $ingredients,
    ) {}
}
