<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBreakTimeModificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('break_time_modifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('break_time_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->dateTime('new_break_start')->nullable();
            $table->dateTime('new_break_end')->nullable();
            $table->tinyInteger('approval_status')->default(0)->comment('0:申請中, 1:承認済');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('break_time_modifications');
    }
}
