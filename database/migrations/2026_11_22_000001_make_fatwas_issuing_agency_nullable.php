<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The scraper often has no issuing authority for a fatwa, and the import
     * API already allows that, so the column must accept NULL.
     */
    public function up(): void
    {
        Schema::table('fatwas', function (Blueprint $table) {
            $table->unsignedBigInteger('issuing_agency')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('fatwas', function (Blueprint $table) {
            $table->unsignedBigInteger('issuing_agency')->nullable(false)->change();
        });
    }
};
