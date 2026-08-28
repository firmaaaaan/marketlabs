<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sample_tests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('laboran_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('code')->unique();
            $table->text('notes')->nullable();
            $table->string('delivery_method')->nullable();
            $table->string('status')->default('pending');
            $table->string('result')->nullable();
            $table->string('result_notes')->nullable();
            $table->string('result_file')->nullable();
            $table->unsignedInteger('total_cost')->default(0);
            $table->string('payment_status')->default('unpaid');
            $table->string('invoice_number')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('tested_at')->nullable();
            $table->timestamp('done_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_tests');
    }
};
