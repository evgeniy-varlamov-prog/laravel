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
        Schema::create('halls', function (Blueprint $table) {
            $table->id();
            $table->string('hall_name')->unique();
            $table->integer('hall_rows');
            $table->integer('hall_places');
            $table->text('hall_config');
            $table->integer('hall_price_standart');
            $table->integer('hall_price_vip');
            $table->tinyInteger('hall_open');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('halls');
    }
};
