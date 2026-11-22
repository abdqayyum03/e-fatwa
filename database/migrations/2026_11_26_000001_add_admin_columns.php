<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 5 (admin area): columns the admin pages read and write.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fatwas', function (Blueprint $table) {
            // The decision passage picked out of full_text (App\Support\FatwaDecision), stored so
            // pages and the quality queue don't re-parse every text on each request.
            $table->text('decision')->nullable()->after('text_from_ocr');
            // An admin looked at this fatwa in the quality queue and it needs nothing more.
            $table->timestamp('quality_checked_at')->nullable()->after('decision');
        });

        Schema::table('feedback', function (Blueprint $table) {
            $table->timestamp('handled_at')->nullable()->after('content');
        });

        Schema::table('users', function (Blueprint $table) {
            // Disabled accounts can't log in (and are logged out if already signed in).
            $table->timestamp('disabled_at')->nullable()->after('is_admin');
        });

        Schema::table('authorities', function (Blueprint $table) {
            $table->string('website')->nullable()->after('state');
        });
    }

    public function down(): void
    {
        Schema::table('fatwas', fn (Blueprint $table) => $table->dropColumn(['decision', 'quality_checked_at']));
        Schema::table('feedback', fn (Blueprint $table) => $table->dropColumn('handled_at'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('disabled_at'));
        Schema::table('authorities', fn (Blueprint $table) => $table->dropColumn('website'));
    }
};
