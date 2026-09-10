<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tools', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type', 20)->default('kesehatan');
            $table->foreignUuid('category_id')->nullable()->constrained('tool_categories')->nullOnDelete();
            $table->string('brand')->nullable();
            $table->string('series')->nullable();
            $table->text('description')->nullable();
            $table->integer('total_stock')->default(0);
            $table->integer('available_stock')->default(0);
            $table->unsignedBigInteger('price_per_day')->default(0);
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->index('type');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tools');
    }
};
