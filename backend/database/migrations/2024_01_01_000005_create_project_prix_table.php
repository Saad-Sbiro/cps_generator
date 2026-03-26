<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_prix', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('projet_id')->constrained('projets')->cascadeOnDelete();
            $table->foreignUuid('prix_catalogue_id')->constrained('prix_catalogues')->cascadeOnDelete();
            $table->integer('numero_prix');
            $table->decimal('quantite', 10, 2)->default(0);
            $table->decimal('prix_unitaire_ht', 12, 2)->default(0);
            $table->decimal('total_ht', 12, 2)->storedAs('quantite * prix_unitaire_ht');
            $table->integer('ordre')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_prix');
    }
};
