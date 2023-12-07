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
        Schema::create('seances', function (Blueprint $table) {
            $table->id();
            $table->string('seance_time');
            $table->integer('seance_start');
            $table->integer('seance_end');
            $table->unsignedBigInteger('seance_hallid');
              $table->foreign('seance_hallid')->references('id')->on('halls')->onDelete('cascade');
            $table->unsignedBigInteger('seance_filmid');
              $table->foreign('seance_filmid')->references('id')->on('films')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seances');
    }
};
