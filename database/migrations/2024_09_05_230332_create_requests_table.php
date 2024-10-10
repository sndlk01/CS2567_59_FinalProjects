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
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('course_id');
            $table->string('status', 1);  //approve , wait ,  not approve
            $table->string('comment', 255)->nullable();
            $table->dateTime('approved_at')->nullable();
            
            $table->foreign('student_id')->references('student_id')->on('course_tas')->onDelete('cascade');
            $table->foreign('course_id')->references('course_id')->on('course_tas')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};