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
        if (Schema::hasTable('custom_field_values')) {
            return;
        }

        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('field_id')->references('id')->on('custom_fields')->cascadeOnDelete();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('account_id');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->index('card_id');
            $table->index('field_id');
            $table->index('conversation_id');
            $table->index('account_id');
            $table->unique(['card_id', 'field_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_field_values');
    }
};
