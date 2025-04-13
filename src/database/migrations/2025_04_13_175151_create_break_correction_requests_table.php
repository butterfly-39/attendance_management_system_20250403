<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBreakCorrectionRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('break_correction_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stamp_correction_request_id')->constrained()->cascadeOnDelete();
            $table->datetime('break_start_time')->nullable();
            $table->datetime('break_end_time')->nullable();
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
        Schema::dropIfExists('break_correction_requests');
    }
}
