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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->char('status');
            $table->char('approve_status')->nullable();
            $table->char('approve_note')->nullable();
            $table->dateTime('approve_at', precision: 0)->nullable();
            $table->integer('approve_user_id')->nullable();
            $table->string('note');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('teaching_id')->nullable(); // สามารถเป็น null ได้เมื่อเป็น extra teaching
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('extra_teaching_id')->nullable(); // สามารถเป็น null ได้เมื่อเป็น teaching ปกติ
            $table->boolean('is_extra')->default(false); // บอกว่าเป็น extra teaching หรือไม่

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('teaching_id')->references('teaching_id')->on('teaching')->onDelete('cascade');
            $table->foreign('extra_teaching_id')->references('extra_class_id')->on('extra_teachings')->onDelete('cascade');

            $table->timestamps();

            // เพิ่ม unique constraint เพื่อป้องกันการลงเวลาซ้ำ
            $table->unique(['teaching_id', 'is_extra'], 'unique_teaching_attendance');
            $table->unique(['extra_teaching_id', 'is_extra'], 'unique_extra_teaching_attendance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
