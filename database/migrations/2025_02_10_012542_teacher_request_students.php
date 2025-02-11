<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teacher_request_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_requests_detail_id')
                ->constrained('teacher_requests_detail')
                ->onDelete('cascade');
            // อ้างอิงข้อมูลนักศึกษาจาก course_tas
            $table->unsignedBigInteger('course_ta_id');
            // ข้อมูลชั่วโมงทำงาน
            $table->integer('teaching_hours')->default(0);      // ช่วยสอน
            $table->integer('prep_hours')->default(0);          // เตรียมการสอน
            $table->integer('grading_hours')->default(0);       // ตรวจงาน
            $table->integer('other_hours')->default(0);         // อื่นๆ
            $table->text('other_duties')->nullable();           // รายละเอียดงานอื่นๆ
            $table->integer('total_hours_per_week');           // รวมชั่วโมงต่อสัปดาห์
            $table->timestamps();

            $table->foreign('course_ta_id')
                ->references('id')
                ->on('course_tas')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
