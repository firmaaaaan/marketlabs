<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrowing_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('borrowing_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('tool_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->unsignedBigInteger('price_per_day')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrowing_items');
    }
};
