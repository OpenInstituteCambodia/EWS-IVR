<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhoneCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('phone_calls', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('max_retries');
            $table->string('phone_number');
            $table->string('status');
            $table->integer('outbound_calls_count');
            $table->dateTime('last_tried_at');
            $table->integer('retry_duration');
            $table->integer('call_flow_id')->unsigned();
            $table->foreign('call_flow_id')->references('id')->on('call_flows');
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
        Schema::table('phone_calls', function(Blueprint $table){
            $table->dropForeign('phone_calls_call_flow_id_foreign');
        });
        Schema::drop('phone_calls');
    }
}
