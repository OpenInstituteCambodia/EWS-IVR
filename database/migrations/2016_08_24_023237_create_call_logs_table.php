<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCallLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('call_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('phone', 20);
            $table->string('status', 40);
            $table->integer('duration')->default(0);
            $table->time('time');
            $table->date('date');
            $table->integer('retries')->default(0);
            $table->integer('project_id')->unsigned();
            $table->integer('call_flow_id')->unsigned();
            $table->integer('max_retry');
            $table->integer('retry_time');
            $table->integer('activity_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('call_flow_id')->references('id')->on('call_flows')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('call_logs', function (Blueprint $table) {
            $table->dropForeign('call_logs' . '_project_id_foreign');
            $table->dropForeign('call_logs' . '_call_flow_id_foreign');
        });
        Schema::drop('call_logs');

    }
}
