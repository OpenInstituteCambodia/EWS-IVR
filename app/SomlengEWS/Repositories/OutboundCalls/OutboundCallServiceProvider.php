<?php
/**
 * Created by PhpStorm.
 * User: keodina
 * Date: 11/4/16
 * Time: 10:13 AM
 */

namespace App\SomlengEWS\Repositories\OutboundCalls;

use App\OutboundCall;
use Illuminate\Support\ServiceProvider;

class OutboundCallServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\SomlengEWS\Repositories\OutboundCalls\OutboundCallRepositoryInterface', function () {
            return new OutboundCallRepository(new OutboundCall());
        });
    }
}