<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Weekly new-fatwa email alerts (opt-in) and preferred interface language. */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('alerts_enabled')->default(false)->after('is_admin');
            $table->json('alert_categories')->nullable()->after('alerts_enabled'); // category ids
            $table->json('alert_states')->nullable()->after('alert_categories');   // state names
            $table->timestamp('last_alert_sent_at')->nullable()->after('alert_states');
            $table->string('locale', 5)->nullable()->after('last_alert_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['alerts_enabled', 'alert_categories', 'alert_states', 'last_alert_sent_at', 'locale']);
        });
    }
};
