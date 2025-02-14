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
        Schema::create('classes', function (Blueprint $table) {
            $table->id('class_id'); // 'id' คอลัมน์หลักที่เป็น AUTO_INCREMENT
            $table->integer('section_num')->unsigned(); // ลบ AUTO_INCREMENT
            $table->string('title', 45); // section
            $table->integer('open_num'); // เปิดรับกี่คน
            $table->integer('enrolled_num'); // สมัครกี่คน
            $table->integer('available_num');  //เหลือกี่คน
            $table->unsignedBigInteger('teacher_id'); // อาจารย์ประจำวิชา
            $table->string('course_id'); 
            $table->unsignedBigInteger('semester_id'); 
            $table->unsignedBigInteger('major_id');
            $table->string('status',3);
            $table->foreign('teacher_id')->references('teacher_id')->on('teachers');
            $table->foreign('course_id')->references('course_id')->on('courses');
            $table->foreign('semester_id')->references('semester_id')->on('semesters');
            $table->foreign('major_id')->references('major_id')->on('major');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
