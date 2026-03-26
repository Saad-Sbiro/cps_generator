<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference')->unique();
            $table->string('intitule');
            $table->date('date_creation');
            $table->decimal('taux_tva', 5, 2)->default(20);
            $table->boolean('inclure_brd_dans_cps')->default(true);
            $table->string('maitre_ouvrage')->nullable();
            $table->text('objet_marche')->nullable();
            $table->string('lieu')->nullable();
            $table->string('delai_execution')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projets');
    }
};
