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
        Schema::create('submission_form', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('media_id');
            $table->string('article_title')->nullable();
            $table->string('thematic_category')->nullable();
            $table->string('keywords')->nullable();
            $table->string('abstract_word_count')->nullable();
            $table->string('video_duration')->nullable();
            $table->string('supplementary_material')->nullable();
            $table->string('graphical_abstract')->nullable();
            $table->string('authors')->nullable();
            $table->string('authors_affiliations')->nullable();
            $table->string('corresponding_author')->nullable();
            $table->string('peer_reviewers')->nullable();
            $table->string('editor_handling_the_submission')->nullable();
            $table->string('digital_object_identifier')->nullable();
            $table->string('submission_date')->nullable();
            $table->string('acceptance_date')->nullable();
            $table->string('revisions_submitted')->nullable();
            $table->string('version_number')->nullable();
            $table->string('copyright_holder')->nullable();
            $table->string('licence_type')->nullable();
            $table->string('institutional_ethics_approval')->nullable();
            $table->string('patient_consent')->nullable();
            $table->string('funding_sources')->nullable();
            $table->string('confilicts_of_interest')->nullable();
            $table->string('corrections_corrigenda')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('media_id')->references('id')->on('media')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_form');
    }
};
