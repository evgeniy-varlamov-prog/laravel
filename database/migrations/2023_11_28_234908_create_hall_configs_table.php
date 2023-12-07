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
        Schema::create('hallconfigs', function (Blueprint $table) {
            $table->id();
            // $table->bigInteger('ticket_seanceid');
            $table->bigInteger('hallconfigs_timestamp');
            $table->text('hallconfigs_configuration');
            $table->unsignedBigInteger('hallconfigs_seanceid');
              $table->foreign('hallconfigs_seanceid')->references('id')->on('seances')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hallconfigs');
    }
};
