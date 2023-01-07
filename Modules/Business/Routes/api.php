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

Route::middleware('auth:api')->get('/business', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('home', 'HomeController@home')->name('home');
    Route::get('google-detector', 'ReviewsController@googleDetector')->name('google-detector');
});

Route::group(['middleware' => ['cors']], function(){
    Route::post('done-me', 'CommonController@ajaxRequestManager');
    Route::post('all-reviews', 'ReviewsController@getReviews');
    Route::post('search-reviews', 'ReviewsController@searchReviews');
    Route::post('connections-data', 'CommonController@getConnectionsData');
    Route::get('company', 'PageController@company')->name('company');
});
