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
        Schema::create('stages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('funnel_id')->constrained()->cascadeOnDelete();
            $table->integer('order')->default(0);
            $table->string('color')->default('#6B7280');
            $table->string('chatwoot_status')->nullable();
            $table->text('automations')->nullable();
            $table->timestamps();

            $table->index('funnel_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stages');
    }
};
