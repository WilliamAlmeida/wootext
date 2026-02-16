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
        Schema::create('internal_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->references('id')->on('internal_chats')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->text('content');
            $table->text('attachments')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->index('chat_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_chat_messages');
    }
};
