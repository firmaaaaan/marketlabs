<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('quota')->nullable();
            $table->decimal('fee', 12, 2)->nullable();
            $table->decimal('discount', 12, 2)->nullable();
            $table->timestamp('registration_deadline')->nullable();
            $table->string('status')->default('draft');
            $table->string('mode')->nullable();
            $table->string('image')->nullable();
            $table->string('poster')->nullable();
            $table->json('form_fields')->nullable();
            $table->json('attendance_fields')->nullable();
            $table->string('certificate_template')->nullable();
            $table->string('certificate_template_back')->nullable();
            $table->string('certificate_font')->nullable();
            $table->json('certificate_layout')->nullable();
            $table->json('certificate_layout_back')->nullable();
            $table->boolean('attendance_enabled')->default(true);
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
