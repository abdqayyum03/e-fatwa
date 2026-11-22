<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Keyword rules that categorise uncategorised fatwas: if a fatwa's title contains
 * one of the keywords, it goes into the rule's category. Lower priority wins.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->text('keywords'); // comma-separated, matched as whole words, any case
            $table->unsignedSmallInteger('priority')->default(50);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_rules');
    }
};
