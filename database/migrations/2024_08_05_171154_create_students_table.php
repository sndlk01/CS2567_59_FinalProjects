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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 256);
            $table->string('student_id', 11);
            $table->string('name', 1024);
            $table->string('card_id', 13);
            $table->string('phone', 11);
            $table->string('email', 1024);
            $table->enum('degree_level', ['bachelor', 'master', 'doctoral'])
                ->default('bachelor')
                ->comment('ระดับปริญญา (ตรี/โท/เอก)');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
