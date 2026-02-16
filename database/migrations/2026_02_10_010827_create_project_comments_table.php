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
        if (Schema::hasTable('project_comments')) {
            return;
        }

        Schema::create('project_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discussion_id')->references('id')->on('project_discussions')->cascadeOnDelete();
            $table->text('content');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->index('discussion_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_comments');
    }
};
