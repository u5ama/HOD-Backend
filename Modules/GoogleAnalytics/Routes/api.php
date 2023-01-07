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

Route::middleware('auth:api')->get('/googleanalytics', function (Request $request) {
    return $request->user();
});


Route::group(['prefix' => 'google-analytics'], function () {
    Route::get('get-login', 'GoogleAnalyticsController@getLogin');

    Route::get('callback', 'GoogleAnalyticsController@callback');

    Route::get('get-accounts', 'GoogleAnalyticsController@getAccounts');

    Route::get('get-web-property', 'GoogleAnalyticsController@getWebProperties');

    Route::get('get-profile-view', 'GoogleAnalyticsController@getProfileViews');

    Route::post('exchange-refresh-token', 'GoogleAnalyticsController@exchangeRefreshToken');

    Route::get('{id}/remove-analytics', 'GoogleAnalyticsCoget-profile-view-cron-jobntroller@removeGoogleAnalytics');

    Route::get('get-profile-view-cron-job', 'GoogleAnalyticsController@getProfileViewsCronJob');
});
