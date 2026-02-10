<?php

use App\Models\Recipe;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recipe_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Recipe::class)->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('version_number');
            $table->string('source');
            $table->json('structured_recipe');
            $table->timestamps();

            $table->unique(['recipe_id', 'version_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_versions');
    }
};
