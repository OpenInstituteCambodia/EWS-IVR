<?php
/**
 * Created by PhpStorm.
 * User: keodina
 * Date: 9/6/16
 * Time: 11:42 AM
 */

namespace App\Twilio\Repositories\QueueCalls;


use App\QueueCall;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class QueueCallServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Twilio\Repositories\QueueCalls\QueueCallRepositoryInterface', function () {
            return new QueueCallRepository(new QueueCall());
        });
    }
}