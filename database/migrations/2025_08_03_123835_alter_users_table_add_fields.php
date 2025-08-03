<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('academic_title')->nullable();
            $table->string('job_description')->nullable();
            $table->string('year_of_graduation')->nullable();
            $table->string('country_of_practices')->nullable();
            $table->string('institution')->nullable();
            $table->string('department')->nullable();
            $table->string('country_of_graduation')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['academic_title', 'job_description', 'year_of_graduation', 'country_of_practices', 'institution', 'department', 'country_of_graduation']);
        });
    }
};
