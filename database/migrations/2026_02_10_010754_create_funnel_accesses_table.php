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
        Schema::create('funnel_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funnel_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->index('funnel_id');
            $table->index('user_id');
            $table->unique(['funnel_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funnel_accesses');
    }
};
