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
        Schema::create('funnels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('account_id');
            $table->integer('order')->default(0);
            $table->string('color')->default('#3B82F6');
            $table->boolean('is_public')->default(false);
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->index('account_id');
            $table->index(['account_id', 'is_system']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funnels');
    }
};
