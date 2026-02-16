<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('card_id');
            $table->timestamp('added_at')->useCurrent();
            $table->unsignedBigInteger('added_by');
            $table->timestamps();

            $table->index('conversation_id');
            $table->index('card_id');
            $table->index('project_id');
            $table->unique(['project_id', 'conversation_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_conversations');
    }
};
