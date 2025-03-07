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
        Schema::create('compensation_rates', function (Blueprint $table) {
            $table->id();
            $table->string('teaching_type'); // 'regular' (ภาคปกติ) หรือ 'special' (ภาคพิเศษ)
            $table->string('class_type');    // 'LECTURE', 'LAB' หรือประเภทอื่นๆ 
            $table->decimal('rate_per_hour', 10, 2); // อัตราต่อชั่วโมง
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            
            $table->index(['teaching_type', 'class_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compensation_rates');
    }
};
