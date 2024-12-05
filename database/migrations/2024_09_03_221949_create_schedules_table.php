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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id('schedule_id');
            $table->enum('class_day', ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']);
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('class_time');
            $table->string('room', 45);
            $table->char('class_type', 1);
            $table->unsignedBigInteger('class_id');

            $table->foreign('class_id')->references('class_id')->on('classes');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
