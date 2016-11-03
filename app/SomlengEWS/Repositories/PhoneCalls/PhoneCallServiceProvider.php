<?php
/**
 * Created by PhpStorm.
 * User: keodina
 * Date: 11/3/16
 * Time: 5:12 PM
 */

namespace App\SomlengEWS\Repositories\PhoneCalls;


use App\PhoneCall;
use Illuminate\Support\ServiceProvider;

class PhoneCallServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\SomlengEWS\Repositories\PhoneCalls\PhoneCallRepositoryInterface', function () {
            return new PhoneCallRepository(new PhoneCall());
        });
    }
}