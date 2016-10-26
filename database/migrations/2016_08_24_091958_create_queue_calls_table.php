<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQueueCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('queue_calls', function (Blueprint $table) {
            $table->string('phone', 40);
            $table->time('time');
            $table->string('call_flow_id');
            $table->integer('retry');
            $table->integer('max_retry');
            $table->integer('retry_time');
            $table->integer('activity_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('queue_calls');
    }
}
