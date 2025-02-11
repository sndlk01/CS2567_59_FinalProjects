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
            $table->foreignId('teacher_request_id')
                ->constrained('teacher_requests')
                ->onDelete('cascade');
            $table->integer('group_number');  // กลุ่มที่ 1, 2
            $table->integer('undergrad_count')->default(0);
            $table->integer('graduate_count')->default(0);
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
