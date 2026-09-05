<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('certificate_batch_status')->nullable()->after('certificate_layout_back');
            $table->unsignedInteger('certificate_batch_total')->default(0)->after('certificate_batch_status');
            $table->unsignedInteger('certificate_batch_done')->default(0)->after('certificate_batch_total');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'certificate_batch_status',
                'certificate_batch_total',
                'certificate_batch_done',
            ]);
        });
    }
};
