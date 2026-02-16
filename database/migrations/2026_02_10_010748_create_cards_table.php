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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->foreignId('stage_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->integer('order')->default(0);
            $table->string('custom_name')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('transferred_from')->nullable();
            $table->timestamps();

            $table->index('stage_id');
            $table->index('account_id');
            $table->index('contact_id');
            $table->unique(['conversation_id', 'account_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
