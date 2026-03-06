<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('section_modeles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('titre');
            $table->enum('type', ['CPS_ADMIN', 'CPS_FIN', 'CPS_TECH_COMMUNE', 'RC']);
            $table->longText('contenu');
            $table->integer('ordre_defaut')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_modeles');
    }
};
