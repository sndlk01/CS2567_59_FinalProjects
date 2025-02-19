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
        Schema::create('curriculums', function (Blueprint $table) {
            $table->unsignedBigInteger('cur_id')->primary();
            $table->string('name_th')->nullable(); 
            $table->string('name_en')->nullable(); 
            $table->char('curr_type', 1); 
            $table->foreignId('head_teacher_id')->nullable()->references('teacher_id')->on('teachers')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curriculums');
    }
};
