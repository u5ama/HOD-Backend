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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['middleware' => ['cors']], function(){

Route::post('register', 'UserController@register');
Route::post('login', 'UserController@authenticate');
Route::post('forget-password', 'UserController@forgotpassword');
Route::get('open', 'DataController@open');

Route::post('update-trial', 'UserController@updateTrial');
Route::post('end-trial', 'UserController@endTrial');

});


Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('user', 'UserController@getAuthenticatedUser');
    Route::get('closed', 'DataController@closed');
    Route::get('logout', 'UserController@logout');
});
