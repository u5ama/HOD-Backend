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

Route::middleware('auth:api')->get('/thirdparty', function (Request $request) {
    return $request->user();
});


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

/*CTM ROUTES*/
Route::group(['middleware' => ['jwt.verify']], function() {
    Route::post('loginCTM', 'CTMController@loginCTM');
    Route::get('get-account-connected', 'CTMController@getAccountConnection');
    Route::get('get-call-details', 'CTMController@getCallsData');
    Route::post('delete-ctm', 'CTMController@deleteCTM');
    Route::get('fb-widget', 'FacebookController@facebookWidgetData');
});
/*CTM ROUTES*/

Route::get('get-stats', 'ThirdPartyController@thirdPartyReviewsStats');

Route::group(['middleware' => 'api', 'prefix' => 'google-place'], function () {
    Route::get('get-place-id', 'GooglePlaceController@getFirstPlaceID');

    // testing reviews demo route
    Route::get('get-reviews', 'GooglePlaceController@getBusinessReviews');
});

Route::group(['middleware' => 'api', 'prefix' => 'social-media'], function () {
    Route::get('/redirect', 'FacebookController@redirect');
    Route::get('callback', 'FacebookController@callback');
    Route::get('login', 'FacebookController@getLogin');

    Route::get('page-detail', 'FacebookController@getUserPageDetail');
    Route::get('page-info', 'FacebookController@getPageDetail');

    Route::get('page-posts', 'FacebookController@getPagePostInfo');

    Route::get('connection-data', 'FacebookController@ConnectionData');

    Route::get('get-access-token', 'FacebookController@getUserAccessToken');

    Route::get('get-token', 'FacebookController@getToken');

    Route::post('manage-social-business-page', 'FacebookController@manageSocialBusinessPages');


});

