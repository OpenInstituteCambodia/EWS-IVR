<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOutboundCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outbound_calls', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('phone_call_id')->unsigned();
            $table->string('call_sid');
            $table->string('status');
            $table->integer('duration');
            $table->foreign('phone_call_id')->references('id')->on('phone_calls');
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
        Schema::drop('outbound_calls');
    }
}
