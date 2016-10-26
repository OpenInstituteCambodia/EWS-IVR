<?php
/**
 * Created by PhpStorm.
 * User: keodina
 * Date: 8/22/16
 * Time: 2:46 PM
 */

namespace App\Twilio\Repositories\CallLogs;


use App\CallLog;
use Illuminate\Support\ServiceProvider;

class CallLogServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Twilio\Repositories\CallLogs\CallLogRepositoryInterface', function () {
            return new CallLogRepository(new CallLog());
        });
    }
}