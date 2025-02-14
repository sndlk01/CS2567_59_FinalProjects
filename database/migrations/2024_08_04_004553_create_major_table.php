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
        Schema::create('major', function (Blueprint $table) {
            $table->unsignedBigInteger('major_id')->primary();
            $table->string('name_th', 1024)->nullable();
            $table->string('name_en', 1024)->nullable();
            $table->enum('major_type', ['N', 'S']); // normal and specials
            $table->unsignedBigInteger('cur_id');
            $table->char('status', 1);

            $table->foreign('cur_id')->references('cur_id')->on('curriculums')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('major');
    }
};
