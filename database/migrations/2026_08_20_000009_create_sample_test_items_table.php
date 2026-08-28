<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sample_test_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sample_test_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('parameter_id')->nullable()->constrained('test_parameters')->nullOnDelete();
            $table->foreignUuid('sample_form_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('sample_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sample_name');
            $table->text('sample_description')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('rate')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_test_items');
    }
};
