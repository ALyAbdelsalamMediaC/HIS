<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE media MODIFY COLUMN status ENUM('published', 'pending', 'inreview', 'declined', 'revise') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE media MODIFY COLUMN status ENUM('published', 'pending', 'inreview', 'declined') NOT NULL DEFAULT 'pending'");
    }
};