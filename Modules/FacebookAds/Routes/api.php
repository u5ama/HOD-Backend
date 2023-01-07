<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/facebookads', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'api', 'prefix' => 'fb-reports'], function () {

    Route::get('/redirect', 'FacebookAdsController@redirect');

    Route::get('callback', 'FacebookAdsController@callback');

    Route::get('login', 'FacebookAdsController@getLogin');

    Route::get('reports-details', 'FacebookAdsController@getAccountReports');

    Route::get('get-access-token', 'FacebookAdsController@getUserAccessToken');

    Route::get('get-token', 'FacebookAdsController@getToken');

});
