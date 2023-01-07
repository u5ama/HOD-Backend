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

Route::prefix('business')->group(function() {
    Route::get('/', 'BusinessController@index');
});
Route::post('done-me', 'CommonController@ajaxRequestManager');

Route::get('business-review/{email}/{secret}/{business}/{reviewID}/{flag?}', 'PageController@showBusinessReview');
Route::get('business-review-complete/{email}/{secret}/{business}', 'PageController@businessReviewComplete');
