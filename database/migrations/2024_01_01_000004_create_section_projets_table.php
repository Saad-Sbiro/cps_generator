<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('section_projets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('projet_id')->constrained('projets')->cascadeOnDelete();
            $table->foreignUuid('section_modele_id')->constrained('section_modeles')->cascadeOnDelete();
            $table->integer('ordre')->default(0);
            $table->longText('contenu_final');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_projets');
    }
};
