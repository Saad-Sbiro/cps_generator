<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ligne_prix_projets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('projet_id')->constrained('projets')->cascadeOnDelete();
            $table->foreignUuid('poste_id')->constrained('catalogue_postes')->cascadeOnDelete();
            $table->integer('numero_prix')->default(1);
            $table->decimal('quantite', 15, 3);
            $table->decimal('prix_unitaire_ht', 15, 2);
            $table->decimal('total_ht', 15, 2)->storedAs('"quantite" * "prix_unitaire_ht"');
            $table->integer('ordre')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ligne_prix_projets');
    }
};
