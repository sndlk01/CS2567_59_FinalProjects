<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('teacher_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('teacher_id');
            $table->enum('payment_type', ['lecture', 'lab', 'both']);
            $table->char('status', 1);
            $table->timestamps();
         
            $table->foreign('class_id')
                ->references('class_id')
                ->on('classes')
                ->onDelete('cascade');
         
            $table->foreign('teacher_id')
                ->references('teacher_id')
                ->on('teachers')
                ->onDelete('cascade');
         });
    }

  
    public function down(): void
    {
        Schema::dropIfExists('teacher_requests');
    }
};
