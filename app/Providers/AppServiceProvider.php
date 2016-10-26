<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('is_wav_file', function ($attribute, $value, $parameters, $validator) {
            Log::info('sound:' . $value->getClientMimeType());
            $type = $value->getClientMimeType();
            if ( $type == 'audio/wav' || $type == 'audio/x-wav' ) {
                return true;
            }
            return false;
        });
        /*Validator::extend('is_text_file', function ($attribute, $value, $parameters, $validator) {
            Log::info('phone:' . $value->getClientOriginalExtension());
            if ($value->getClientOriginalExtension() == 'json') {
                return true;
            }
            return false;
        });*/
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }
}
