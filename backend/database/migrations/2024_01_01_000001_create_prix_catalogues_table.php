<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prix_catalogues', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('categorie');
            $table->string('sous_categorie')->nullable();
            $table->text('designation');
            $table->string('unite');
            $table->decimal('prix_unitaire_ht_defaut', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prix_catalogues');
    }
};
