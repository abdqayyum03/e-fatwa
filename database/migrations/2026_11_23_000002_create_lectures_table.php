<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Kuliah, ceramah and other religious events at mosques. */
    public function up(): void
    {
        Schema::create('lectures', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type', 30)->default('Kuliah'); // Kuliah, Ceramah, Tazkirah, Forum
            $table->text('description')->nullable();
            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at')->nullable();
            $table->foreignId('mosque_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('preacher_id')->nullable()->constrained()->nullOnDelete();
            $table->string('venue')->nullable(); // used when the event isn't at a listed mosque
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lectures');
    }
};
