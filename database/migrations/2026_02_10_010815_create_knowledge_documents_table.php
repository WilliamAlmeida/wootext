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
        Schema::create('knowledge_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_base_id')->constrained()->cascadeOnDelete();
            $table->string('file_name');
            $table->string('original_name');
            $table->integer('file_size');
            $table->string('mime_type');
            $table->string('file_path');
            $table->text('content');
            $table->unsignedBigInteger('uploaded_by');
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();

            $table->index('knowledge_base_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_documents');
    }
};
