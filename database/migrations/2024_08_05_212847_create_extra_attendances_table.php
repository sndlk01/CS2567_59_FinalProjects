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
        Schema::create('extra_attendances', function (Blueprint $table) {
            $table->id();
            $table->string('detail', 255);
            $table->dateTime('start_work');
            $table->integer('duration');
            $table->char('approve_status')->nullable();
            $table->char('approve_note')->nullable();
            $table->dateTime('approve_at', precision: 0)->nullable();
            $table->integer('approve_user_id')->nullable();
            $table->char('class_type', 1);
            $table->unsignedBigInteger('student_id');
            // $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('class_id');
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('class_id')->references('class_id')->on('classes')->onDelete('cascade');
            // $table->foreign('course_id')->references('course_id')->on('courses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extra_attendances');
    }
};
