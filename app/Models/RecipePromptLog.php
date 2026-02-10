<?php

namespace App\Models;

use App\RecipePromptType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipePromptLog extends Model
{
    /** @use HasFactory<\Database\Factories\RecipePromptLogFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'recipe_id',
        'type',
        'prompt',
        'response',
        'provider',
        'model',
        'request',
        'meta',
        'prompt_tokens',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'request' => 'array',
            'response' => 'array',
            'type' => RecipePromptType::class,
        ];
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
