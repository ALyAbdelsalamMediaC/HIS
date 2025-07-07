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
       Schema::create('check_updates', function (Blueprint $table) {
            $table->tinyInteger('ios_version')->nullable()->unsigned();
            $table->tinyInteger('android_version')->nullable()->unsigned();
            $table->boolean('ios')->default(true);
            $table->boolean('android')->default(true);
            $table->string('android_link')->nullable();
            $table->string('ios_link')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('check_updates');
    }
};
