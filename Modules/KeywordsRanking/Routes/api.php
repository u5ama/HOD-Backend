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

Route::middleware('auth:api')->get('/keywordsranking', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['jwt.verify']], function() {

    Route::get('get-keywords-data', 'KeywordsRankingController@getKeywordsData');

    Route::get('get-keywords-improvements', 'KeywordsRankingController@keywordImprovements');
});

Route::get('get-business-data', 'KeywordsRankingController@businessData');

//for job
Route::get('getTracking', 'KeywordsRankingController@trackKeywords');

Route::post('add-se-project', 'KeywordsRankingController@addProject');

Route::post('delete-se-project', 'KeywordsRankingController@deleteProject');

Route::post('add-se-keywords', 'KeywordsRankingController@addUserKeywords');

Route::post('delete-se-keyword', 'KeywordsRankingController@deleteKeyword');
