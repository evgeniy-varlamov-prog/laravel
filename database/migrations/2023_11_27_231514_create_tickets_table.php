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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            // $table->bigInteger('ticket_seanceid');
            $table->string('ticket_date');
            $table->string('ticket_time');
            $table->string('ticket_filmname');
            $table->string('ticket_hallname');
            $table->integer('ticket_row');
            $table->integer('ticket_place');
            $table->integer('ticket_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
