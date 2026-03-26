<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_articles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('projet_id')->constrained('projets')->cascadeOnDelete();
            $table->foreignUuid('article_id')->constrained('articles')->cascadeOnDelete();
            $table->foreignUuid('article_variant_id')
                  ->nullable()
                  ->constrained('article_variants')
                  ->nullOnDelete();
            $table->integer('ordre')->default(0);
            $table->longText('contenu_final');          // placeholder-resolved final content
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_articles');
    }
};
