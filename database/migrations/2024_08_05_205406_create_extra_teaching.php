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
        Schema::create('extra_teachings', function (Blueprint $table) {
            $table->id('extra_class_id');
            $table->string('title', 1024);
            $table->string('detail', 1024);
            $table->char('opt_status');
            $table->char('status');
            $table->date('class_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration');
            $table->unsignedBigInteger('teacher_id');
            $table->integer('holiday_id')->nullable();
            $table->unsignedBigInteger('teaching_id');
            $table->unsignedBigInteger('class_id');
            $table->timestamps();

            $table->foreign('teacher_id')->references('teacher_id')->on('teachers')->onDelete('cascade');
            $table->foreign('teaching_id')->references('teaching_id')->on('teaching')->onDelete('cascade');
            $table->foreign('class_id')->references('class_id')->on('classes')->onDelete('cascade');
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
