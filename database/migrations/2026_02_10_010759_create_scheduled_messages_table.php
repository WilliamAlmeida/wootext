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
        Schema::create('scheduled_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('account_id');
            $table->text('message');
            $table->timestamp('scheduled_at');
            $table->string('status')->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->text('api_token')->nullable();
            $table->text('jwt_access_token')->nullable();
            $table->string('jwt_client')->nullable();
            $table->string('jwt_uid')->nullable();
            $table->string('jwt_expiry')->nullable();
            $table->string('jwt_token_type')->nullable();
            $table->text('attachments')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'account_id']);
            $table->index(['status', 'scheduled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_messages');
    }
};
