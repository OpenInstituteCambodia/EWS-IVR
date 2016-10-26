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
    Route::any('ews-ivr-calling/{sound}', ['as' => 'ews-ivr-calling', 'uses' => 'EwsIVRController@ivrCalling']);
    Route::any('ews-call-status-check/{retry}/{activityId}/{maxRetry}/{retryTime}/{callFlowId}', ['as' => 'ews-call-status-check', 'uses' => 'EwsIVRController@statusChecking']);
}
);

/* Routes for BongPheak project IVR calling */
Route::group(['prefix' => 'bongPheakIVR'], function () {
    Route::any('job-offer', ['as' => 'job-offer', 'uses' => 'BongPheakIVRController@playJobOffer']);
    Route::any('job-offer-menu', ['as' => 'job-offer-menu', 'uses' => 'BongPheakIVRController@showJobOfferMenu']);
    Route::any('skill-clarification', ['as' => 'skill-clarification', 'uses' => 'BongPheakIVRController@playSkillClarification']);
    Route::any('skill-clarification-menu', ['as' => 'skill-clarification-menu', 'uses' => 'BongPheakIVRController@showSkillClarificationMenu']);
    Route::any('job-menu-again', ['as' => 'job-menu-again', 'uses' => 'BongPheakIVRController@playJobMenuAgain']);
    Route::any('status-checking', ['as' => 'status-checking', 'uses' => 'BongPheakIVRController@statusChecking']);
}
);
Route::auth();

/*Route::get('/home', 'HomeController@index');*/

Route::group(['prefix' => 'api/v1', 'middleware' => ['cors', 'auth:api']], function () {
    Route::post('processDataUpload', ['as' => 'process-data-upload', 'uses' => 'ApiController@processDataUpload']);
    Route::post('bongPheakCallAPI', ['as' => 'bong-pheak-call-api', 'uses' => 'BongPheakIVRController@bongPheakCallAPI']);
}
);

