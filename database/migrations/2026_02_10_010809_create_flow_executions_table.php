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
        Schema::create('flow_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flow_id')->references('id')->on('chatbot_flows')->cascadeOnDelete();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('account_id');
            $table->string('status')->default('queued');
            $table->string('current_node_id')->nullable();
            $table->text('context')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('flow_id');
            $table->index('conversation_id');
            $table->index('account_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flow_executions');
    }
};
