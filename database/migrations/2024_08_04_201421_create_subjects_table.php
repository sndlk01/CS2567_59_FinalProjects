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
        Schema::create('subjects', function (Blueprint $table) {
            $table->string('subject_id', 10)->primary();
            $table->string('name_th', 1024); 
            $table->string('name_en', 1024); 
            $table->integer('credits'); 
            $table->string('weight', 20); 
            $table->string('detail', 1024); 
            $table->foreignId('cur_id')->constrained('curriculums')->onDelete('cascade'); 
            $table->char('status', 1);
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};