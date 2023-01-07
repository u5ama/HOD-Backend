<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('keywordsranking')->group(function() {
    Route::get('/', 'KeywordsRankingController@index');
});
Route::get('keywords', 'KeywordsRankingController@keywordsHome')->name('keywords');

Route::get('add-keyword', 'KeywordsRankingController@addKeywords')->name('add-keyword');

Route::get('keyword/edit/{id?}', 'KeywordsRankingController@editKeywords')->name('keyword/edit');
