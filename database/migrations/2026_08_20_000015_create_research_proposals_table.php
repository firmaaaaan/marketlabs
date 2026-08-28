<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_proposals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('title');
            $table->string('field')->nullable();
            $table->text('description')->nullable();
            $table->text('objectives')->nullable();
            $table->string('institution')->nullable();
            $table->string('customer_type', 50)->nullable();
            $table->string('nim_nip')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('pending');
            $table->text('admin_notes')->nullable();
            $table->string('document_path')->nullable();
            $table->string('letter_path')->nullable();
            $table->string('replacement_letter_path')->nullable();
            $table->integer('bench_fee')->nullable();
            $table->string('bench_fee_level')->nullable();
            $table->string('bench_fee_type')->nullable();
            $table->string('bench_fee_category')->nullable();
            $table->boolean('needs_laboran')->default(false);
            $table->foreignUuid('laboratorium_id')->nullable()->constrained('laboratoriums')->nullOnDelete();
            $table->foreignUuid('laboran_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('laboran_fee')->nullable();
            $table->unsignedInteger('penalty')->default(0);
            $table->string('penalty_note')->nullable();
            $table->string('payment_status')->default('unpaid');
            $table->string('invoice_number')->nullable()->unique();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('ongoing_at')->nullable();
            $table->timestamp('done_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('research_proposals');
    }
};
