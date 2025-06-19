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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('views')->default(0);
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->json('assigned_to')->nullable();
            $table->text('duration')->nullable();
            $table->string('pdf')->nullable();
            $table->string('thumbnail_path');
            $table->string('image_path')->nullable();
            $table->enum('status', ['published', 'pending', 'inreview', 'declined']);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
