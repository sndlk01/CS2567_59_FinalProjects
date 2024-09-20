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
            $table->id(); // 'id' คอลัมน์หลักที่เป็น AUTO_INCREMENT
            $table->integer('section_num')->unsigned(); // ลบ AUTO_INCREMENT
            $table->string('title', 45);
            $table->unsignedBigInteger('class_type_id');
            $table->integer('open_num');
            $table->integer('enrolled_num');
            $table->integer('available_num'); 
            $table->unsignedBigInteger('teachers_id');
            $table->unsignedBigInteger('courses_id'); 
            $table->unsignedBigInteger('semesters_id'); 
            $table->unsignedBigInteger('major_id');

            $table->foreign('class_type_id')->references('id')->on('class_type');
            $table->foreign('teachers_id')->references('id')->on('teachers');
            $table->foreign('courses_id')->references('id')->on('courses');
            $table->foreign('semesters_id')->references('id')->on('semesters');
            $table->foreign('major_id')->references('id')->on('major');

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
