<?php

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
        Schema::table('prix_catalogues', function (Blueprint $table) {
            $table->string('type_poste')->default('quantitatif')->after('unite');
            $table->text('description_technique')->nullable()->after('type_poste');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prix_catalogues', function (Blueprint $table) {
            $table->dropColumn(['type_poste', 'description_technique']);
        });
    }
};
