<?php

namespace App\Models;

use App\Data\RecipeData;
use App\RecipeVersionSource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeVersion extends Model
{
    /** @use HasFactory<\Database\Factories\RecipeVersionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'recipe_id',
        'version_number',
        'source',
        'structured_recipe',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'source' => RecipeVersionSource::class,
            'structured_recipe' => RecipeData::class,
        ];
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
}
