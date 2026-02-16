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
        Schema::create('user_resource_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('kanban_access')->default(true);
            $table->boolean('conexoes_access')->default(true);
            $table->boolean('chats_internos_access')->default(true);
            $table->boolean('projects_access')->default(true);
            $table->boolean('chatbot_flows_access')->default(true);
            $table->boolean('permissoes_access')->default(false);
            $table->timestamps();

            $table->index('account_id');
            $table->index('user_id');
            $table->index(['account_id', 'user_id']);
            $table->unique(['account_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_resource_permissions');
    }
};
