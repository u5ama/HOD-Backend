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

Route::middleware('auth:api')->get('/googleadwords', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'google-sense'], function () {

    Route::get('adsCallback', 'GoogleAdwordsController@callback');

    Route::get('my-login', 'GoogleAdwordsController@getLogin');

    Route::get('get-sense-accounts', 'GoogleAdwordsController@AdsAccounts')->name('get-sense-accounts');

    Route::get('get-sense-web', 'GoogleAdwordsController@getAdsWebProperties')->name('get-sense-web');

    Route::get('get-profile-data', 'GoogleAdwordsController@getAllAdsData')->name('get-profile-data');

    Route::get('get-campaign', 'GoogleAdwordsController@CampaignService')->name('get-campaign');

    Route::get('get-sense-data', 'GoogleAdwordsController@getAdsAllData')->name('get-sense-data');

    Route::get('get-sense-stats', 'GoogleAdwordsController@getAdsStatData')->name('get-sense-stats');

    Route::get('get-sense-graphData', 'GoogleAdwordsController@adsSpendWidget')->name('get-sense-graphData');
});
