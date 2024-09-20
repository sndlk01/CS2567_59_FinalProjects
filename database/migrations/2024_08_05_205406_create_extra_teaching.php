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
        Schema::create('extra_teaching', function (Blueprint $table) {
            $table->id();
            $table->string('title', length: 1024);
            $table->string('detail', length: 1024);
            $table->char('opt_status');
            $table->char('status');
            $table->date('class_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration');
            $table->unsignedBigInteger('teacher_id');
            $table->integer('holiday_id');
            $table->unsignedBigInteger('teaching_id');
            $table->unsignedBigInteger('class_id');

            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
            $table->foreign('teaching_id')->references('id')->on('teaching')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extra_teaching');
    }
};
