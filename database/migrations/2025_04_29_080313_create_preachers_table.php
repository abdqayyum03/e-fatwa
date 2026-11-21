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
        Schema::create('preachers', function (Blueprint $table) {
            $table->id();
            $table->string('preacher_name');
            $table->text('certification_details')->nullable();
            $table->string('location')->nullable();
            $table->string('state')->nullable();
            $table->foreignId('authority_id')->nullable()->constrained('authorities');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preachers');
    }
};
