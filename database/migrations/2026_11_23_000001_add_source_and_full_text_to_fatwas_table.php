<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fatwas', function (Blueprint $table) {
            // Previously the scraper stored the PDF link in "content"; content is now
            // an optional summary, and the links and text have their own columns.
            $table->text('content')->nullable()->change();
            $table->string('title', 500)->change(); // some Selangor titles exceed 255 chars

            $table->string('source_url', 500)->nullable()->unique()->after('content');
            $table->string('pdf_url', 500)->nullable()->after('source_url');
            $table->longText('full_text')->nullable()->after('pdf_url');
            $table->string('gazette_no', 100)->nullable()->after('date_issued');
            $table->string('status', 30)->nullable()->after('gazette_no'); // e.g. "Diwartakan"
            $table->unsignedSmallInteger('issued_year')->nullable()->index()->after('status');

            $table->fullText(['title', 'full_text']);
        });
    }

    public function down(): void
    {
        Schema::table('fatwas', function (Blueprint $table) {
            $table->dropFullText(['title', 'full_text']);
            $table->dropUnique(['source_url']);
            $table->dropIndex(['issued_year']);
            $table->dropColumn(['source_url', 'pdf_url', 'full_text', 'gazette_no', 'status', 'issued_year']);
        });
    }
};
