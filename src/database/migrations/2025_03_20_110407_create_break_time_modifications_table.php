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
            $table->foreignId('break_time_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_modification_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->time('new_break_start');
            $table->time('new_break_end');
            $table->tinyInteger('approval_status')->default(0)->comment('0:申請中, 1:承認済');
            $table->foreignId('approved_by')->nullable()->constrained('admins')->nullOnDelete();
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
