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
        Schema::create('teacher_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id');
            $table->string('course_id');
            $table->enum('status', ['W', 'A', 'R']);            // approve, wait, not approve (rejects)
            $table->enum('payment_type', ['lecture', 'lab', 'both']);
            $table->text('admin_comment')->nullable();
            $table->timestamp('admin_processed_at')->nullable();
            $table->unsignedBigInteger('admin_user_id')->nullable();
            $table->timestamps();

            $table->foreign('teacher_id')
                ->references('teacher_id')
                ->on('teachers')  
                ->onDelete('cascade');

            $table->foreign('course_id')
                ->references('course_id')
                ->on('courses')
                ->onDelete('cascade');

            $table->foreign('admin_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('teacher_requests');
    }
};
