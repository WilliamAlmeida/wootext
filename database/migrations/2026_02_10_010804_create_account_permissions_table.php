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
        Schema::create('account_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id')->unique();
            $table->boolean('kanban_enabled')->default(true);
            $table->boolean('chats_internos_enabled')->default(true);
            $table->boolean('conexoes_enabled')->default(true);
            $table->boolean('projects_enabled')->default(true);
            $table->boolean('chatbot_flows_enabled')->default(true);
            $table->text('allowed_providers')->nullable();
            $table->timestamps();

            $table->index('account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_permissions');
    }
};
