<?php

namespace App\Models;

use App\Data\RecipeData;
use App\RecipeStatus;
use App\RecipeVersionSource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
    /** @use HasFactory<\Database\Factories\RecipeFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'status',
        'title',
        'description',
        'servings',
        'current_version_id',
        'saved_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'saved_at' => 'datetime',
            'status' => RecipeStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(RecipeVersion::class);
    }

    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(RecipeVersion::class, 'current_version_id');
    }

    public function promptLogs(): HasMany
    {
        return $this->hasMany(RecipePromptLog::class);
    }

    public function addVersion(RecipeData $recipe, RecipeVersionSource $source): RecipeVersion
    {
        $nextVersion = (int) $this->versions()->max('version_number') + 1;

        $version = $this->versions()->create([
            'version_number' => $nextVersion,
            'source' => $source,
            'structured_recipe' => $recipe,
        ]);

        $this->forceFill([
            'current_version_id' => $version->id,
            'title' => $recipe->title,
            'description' => $recipe->description,
            'servings' => $recipe->servings,
        ])->save();

        return $version;
    }
}
