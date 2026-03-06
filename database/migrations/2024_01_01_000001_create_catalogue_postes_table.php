<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogue_postes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('designation');
            $table->string('unite');
            $table->decimal('prix_unitaire_ht_defaut', 15, 2)->default(0);
            $table->text('description_technique');
            $table->string('categorie')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogue_postes');
    }
};
