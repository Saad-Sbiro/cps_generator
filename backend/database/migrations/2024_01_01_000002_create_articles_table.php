<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();           // e.g. ART1, RC_ART1
            $table->string('titre');                    // e.g. "Article 1: Objet du marché"
            $table->enum('type', ['CPS_ADMIN', 'CPS_FIN', 'CPS_TECH_COMMUNE', 'RC']);
            $table->longText('contenu');
            $table->integer('ordre_defaut')->default(0);
            $table->timestamps();
        });

        if (Schema::hasTable('article_variants')) {
            Schema::table('article_variants', function (Blueprint $table) {
                $table->foreign('article_id')->references('id')->on('articles')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('article_variants')) {
            Schema::table('article_variants', function (Blueprint $table) {
                $table->dropForeign(['article_id']);
            });
        }

        Schema::dropIfExists('articles');
    }
};
