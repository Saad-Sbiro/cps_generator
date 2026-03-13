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
        // Pivot table for project collaborators
        Schema::create('projet_user', function (Blueprint $table) {
            $table->uuid('projet_id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('viewer'); // 'editor', 'viewer'
            $table->timestamps();

            $table->primary(['projet_id', 'user_id']);
            $table->foreign('projet_id')->references('id')->on('projets')->cascadeOnDelete();
        });

        // Invitations table
        Schema::create('projet_invitations', function (Blueprint $table) {
            $table->id();
            $table->uuid('projet_id');
            $table->foreignId('inviter_id')->constrained('users')->cascadeOnDelete();
            $table->string('email');
            $table->string('role')->default('viewer');
            $table->timestamps();

            $table->foreign('projet_id')->references('id')->on('projets')->cascadeOnDelete();
            $table->unique(['projet_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projet_invitations');
        Schema::dropIfExists('projet_user');
    }
};
