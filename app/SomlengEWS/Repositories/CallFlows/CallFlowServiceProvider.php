<?php
/**
 * Created by PhpStorm.
 * User: keodina
 * Date: 11/3/16
 * Time: 4:26 PM
 */

namespace App\SomlengEWS\Repositories\CallFlows;


use App\CallFlow;
use Illuminate\Support\ServiceProvider;

class CallFlowServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\SomlengEWS\Repositories\CallFlows\CallFlowRepositoryInterface', function () {
            return new  CallFlowRepository(new CallFlow());
        });
    }
}