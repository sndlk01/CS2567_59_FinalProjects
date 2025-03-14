<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnnouncesTable extends Migration
{
    public function up()
    {
        Schema::create('announces', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->unsignedBigInteger('semester_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('semester_id')
                ->references('semester_id')
                ->on('semesters')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('announces', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
        });

        Schema::dropIfExists('announces');
    }
}
