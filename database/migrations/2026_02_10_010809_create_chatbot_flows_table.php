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
        Schema::create('chatbot_flows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('account_id');
            $table->boolean('is_active')->default(false);
            $table->string('trigger');
            $table->text('flow_data');
            $table->unsignedBigInteger('agent_bot_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->index('account_id');
            $table->index(['account_id', 'is_active']);
            $table->index('agent_bot_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_flows');
    }
};
