<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_checkups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('type_id')->constrained('health_test_types')->restrictOnDelete();
            $table->foreignUuid('examiner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('code')->unique();
            $table->date('booking_date');
            $table->unsignedInteger('queue_number');
            $table->string('purpose')->nullable();
            $table->string('status')->default('pending');
            $table->string('result')->nullable();
            $table->text('result_notes')->nullable();
            $table->string('result_file')->nullable();
            $table->string('payment_status')->default('unpaid');
            $table->string('invoice_number')->nullable()->unique();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('done_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();

            $table->unique(['booking_date', 'queue_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_checkups');
    }
};
