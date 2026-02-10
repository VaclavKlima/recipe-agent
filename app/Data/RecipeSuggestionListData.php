<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class RecipeSuggestionListData extends Data
{
    public function __construct(
        #[DataCollectionOf(RecipeSuggestionData::class)]
        public DataCollection $suggestions,
    ) {}
}
