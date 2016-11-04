<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCallFlowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('call_flows', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id')->unsigned();
            $table->string('sound_file_path');
            $table->string('contact_file_path');
            $table->string('activity_id');
            $table->integer('retry_duration');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
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
        Schema::table('call_flows', function (Blueprint $table) {
            $table->dropForeign('call_flows' . '_project_id_foreign');
        });
        Schema::drop('call_flows');
    }
}
