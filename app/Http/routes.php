<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

/* Routes for EWS project IVR calling */
Route::group(['prefix' => 'ewsIVR'], function () {
    Route::any('ews-ivr-calling', ['as' => 'ews-ivr-calling', 'uses' => 'EwsIVRController@ivrCalling']);
    Route::any('ews-call-status-check', ['as' => 'ews-call-status-check', 'uses' => 'EwsIVRController@statusChecking']);
}
);

Route::auth();

/*Route::get('/home', 'HomeController@index');*/

Route::group(['prefix' => 'api/v1', 'middleware' => ['cors', 'auth:api']], function () {
    Route::post('processDataUpload', ['as' => 'process-data-upload', 'uses' => 'EwsIVRController@processDataUpload']);
}
);

