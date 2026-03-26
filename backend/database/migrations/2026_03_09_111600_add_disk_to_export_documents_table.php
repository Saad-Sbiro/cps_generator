<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('export_documents', function (Blueprint $table) {
            $table->string('disk')->default('local')->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('export_documents', function (Blueprint $table) {
            $table->dropColumn('disk');
        });
    }
};
