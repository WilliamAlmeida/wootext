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
        Schema::create('card_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('account_id');
            $table->foreignId('template_id')->nullable()->references('id')->on('item_templates')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('value')->default(0);
            $table->integer('quantity')->default(1);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index('card_id');
            $table->index('conversation_id');
            $table->index('account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_items');
    }
};
