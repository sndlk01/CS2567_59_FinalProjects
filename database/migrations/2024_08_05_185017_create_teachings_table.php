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
        Schema::create('teaching', function (Blueprint $table) {
            $table->id();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('duration');
            $table->unsignedBigInteger('class_type_id');
            $table->char('status', 1);
            $table->unsignedBigInteger('classes_id'); 
            $table->unsignedBigInteger('teachers_id'); 

            $table->foreign('class_type_id')->references('id')->on('class_type')->onDelete('cascade');
            $table->foreign('classes_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('teachers_id')->references('id')->on('teachers')->onDelete('cascade');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teaching');
    }
};
