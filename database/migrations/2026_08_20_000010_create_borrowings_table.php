<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrowings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('invoice_number')->nullable()->unique();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'borrowed',
                'returned',
                'cancelled',
            ])->default('pending')->index();
            $table->enum('borrower_type', ['internal', 'eksternal'])->default('internal');
            $table->string('nim_nip')->nullable();
            $table->string('institution')->nullable();
            $table->text('purpose')->nullable();
            $table->date('borrow_date');
            $table->date('return_date');
            $table->unsignedInteger('discount')->default(0);
            $table->unsignedInteger('penalty')->default(0);
            $table->text('pickup_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->string('document_path')->nullable();
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid')->index();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrowings');
    }
};
