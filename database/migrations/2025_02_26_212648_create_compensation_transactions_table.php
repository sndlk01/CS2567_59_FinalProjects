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
        Schema::create('compensation_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_id'); // เชื่อมโยงกับตาราง attendances
            $table->unsignedBigInteger('compensation_rate_id'); // เชื่อมโยงกับตาราง compensation_rates
            $table->decimal('total_amount', 10, 2); // จำนวนเงินที่คำนวณได้
            $table->dateTime('calculated_at'); // เวลาที่คำนวณ
            $table->unsignedBigInteger('calculated_by'); // ผู้ที่ทำการคำนวณ (user_id)
            $table->timestamps();
        
            $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('cascade');
            $table->foreign('compensation_rate_id')->references('id')->on('compensation_rates')->onDelete('cascade');
            $table->foreign('calculated_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compensation_transactions');
    }
};
