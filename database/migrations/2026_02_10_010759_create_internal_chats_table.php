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
        Schema::create('internal_chats', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->index('account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_chats');
    }
};
