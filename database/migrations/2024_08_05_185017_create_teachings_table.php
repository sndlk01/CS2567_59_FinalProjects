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
        Schema::create('teaching', function (Blueprint $table) {
            $table->unsignedBigInteger('teaching_id')->primary();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('duration');
            $table->char('class_type', 1);
            $table->char('status', 1);
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('teacher_id');

            $table->foreign('class_id')->references('class_id')->on('classes')->onDelete('cascade');
            $table->foreign('teacher_id')->references('teacher_id')->on('teachers')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teaching');
    }
};
