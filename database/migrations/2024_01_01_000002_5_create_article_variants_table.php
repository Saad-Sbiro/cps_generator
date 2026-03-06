<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_variants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('article_id')->constrained('prix_catalogues')->cascadeOnDelete();
            $table->string('label');                    // e.g. "Variante standard", "Variante marché public"
            $table->longText('contenu');                // variant-specific content (may include {{placeholders}})
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_variants');
    }
};
