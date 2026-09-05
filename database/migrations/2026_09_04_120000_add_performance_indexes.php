<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->index('status');
            $table->index('certificate_status');
            $table->index('attended_at');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->index('certificate_batch_status');
        });
    }

    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['certificate_status']);
            $table->dropIndex(['attended_at']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['certificate_batch_status']);
        });
    }
};
