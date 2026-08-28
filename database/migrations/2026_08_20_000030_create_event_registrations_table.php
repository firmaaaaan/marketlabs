<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_registrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('registered');
            $table->json('answers')->nullable();
            $table->string('attendance_token')->nullable()->unique();
            $table->json('attendance_answers')->nullable();
            $table->timestamp('attended_at')->nullable();
            $table->string('certificate_number')->nullable()->unique();
            $table->string('certificate_path')->nullable();
            $table->string('certificate_back_path')->nullable();
            $table->timestamp('certificate_generated_at')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
    }
};