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
        Schema::create('courses', function (Blueprint $table) {
            $table->string('course_id')->primary();
            $table->char('status', 1);
            $table->string('subject_id');
            $table->unsignedBigInteger('owner_teacher_id');
            $table->unsignedBigInteger('semester_id');
            $table->unsignedBigInteger('major_id')->nullable();
            $table->unsignedBigInteger('cur_id');
            $table->foreign('subject_id')->references('subject_id')->on('subjects');
            $table->foreign('owner_teacher_id')->references('teacher_id')->on('teachers');
            $table->foreign('semester_id')->references('semester_id')->on('semesters');
            $table->foreign('major_id')->references('major_id')->on('major');
            $table->foreign('cur_id')->references('cur_id')->on('curriculums');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
