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
        Schema::create('teacher_requests_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_request_id')->constrained('teacher_requests')->onDelete('cascade');
            $table->string('student_code', 11);
            $table->string('name', 255);
            $table->string('phone', 11);
            $table->enum('education_level', ['bachelor', 'master']);
            $table->integer('total_hours_per_week');
            $table->integer('lecture_hours');
            $table->integer('lab_hours');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_requests_detail');
    }
};
