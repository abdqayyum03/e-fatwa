<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Marks fatwas whose text was read from scanned pages by OCR (may contain recognition errors). */
    public function up(): void
    {
        Schema::table('fatwas', function (Blueprint $table) {
            $table->boolean('text_from_ocr')->default(false)->after('full_text');
        });
    }

    public function down(): void
    {
        Schema::table('fatwas', function (Blueprint $table) {
            $table->dropColumn('text_from_ocr');
        });
    }
};
