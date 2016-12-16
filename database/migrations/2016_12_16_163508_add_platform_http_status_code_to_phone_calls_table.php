<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPlatformHttpStatusCodeToPhoneCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('phone_calls', function (Blueprint $table) {
            $table->string('platform_http_status_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('phone_calls', function (Blueprint $table) {
            $table->dropColumn('platform_http_status_code');
        });
    }
}
