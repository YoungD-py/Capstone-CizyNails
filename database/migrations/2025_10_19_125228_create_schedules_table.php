<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('time_slot');
            $table->integer('nails_art_booked')->default(0); // 0-2
            $table->integer('eyelash_booked')->default(0); // 0-1
            $table->timestamps();
            $table->unique(['date', 'time_slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
