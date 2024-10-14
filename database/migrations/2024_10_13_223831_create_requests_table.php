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
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_ta_class_id');
            $table->string('status', 1);  // approve, wait, not approve
            $table->string('comment', 255)->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('course_ta_class_id')
                ->references('id')
                ->on('course_ta_classes')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};

