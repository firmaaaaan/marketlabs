<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_proposal_tools', function (Blueprint $table) {
            $table->foreignUuid('research_proposal_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('tool_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->unsignedSmallInteger('days')->default(1);
            $table->timestamps();

            $table->primary(['research_proposal_id', 'tool_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('research_proposal_tools');
    }
};
