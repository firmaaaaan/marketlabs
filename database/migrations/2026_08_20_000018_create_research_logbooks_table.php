<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_logbooks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('research_proposal_id')->constrained()->cascadeOnDelete();
            $table->date('log_date');
            $table->text('note');
            $table->text('obstacle')->nullable();
            $table->timestamps();

            $table->index(['research_proposal_id', 'log_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('research_logbooks');
    }
};
