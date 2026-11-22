<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Fatwas a user has saved ("Simpanan saya"). */
    public function up(): void
    {
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fatwa_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'fatwa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookmarks');
    }
};
