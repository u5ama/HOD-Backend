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

Route::prefix('admin')->group(function() {
    Route::get('/', 'AdminController@index');
});


/********************* system administrators ******************/

Route::group(['prefix' => 'admin'], function () {
    Route::get('login', 'AdminController@showLoginView')->name('admin-login');
    Route::post('login', 'AdminController@login')->name('post-login');
    Route::get('logout', 'AdminController@logOut')->name('admin.logout');
});

Route::group(['middleware' => 'adminAllow', 'prefix' => 'admin'], function () {

    Route::get('dashboard', 'DashboardController@dashboard')->name('adminDashboard');

    Route::get('deleted-users', 'DashboardController@deletedUsers')->name('deletedUsers');

    Route::get('user/edit/{id?}', 'DashboardController@editUser')->name('userEdit');

    Route::put('update-user/{id}', 'DashboardController@updateUser')->name('update.user');

    Route::get('csm', 'CSMController@index')->name('csm');

    Route::get('csm-create', 'CSMController@create')->name('csm.create');
    Route::post('csm-create', 'CSMController@store')->name('csm.store');
    Route::get('csm/{id}/edit', 'CSMController@edit')->name('csm.edit');
    Route::post('csm-update/{id}', 'CSMController@update')->name('csm.update');
    Route::post('deleteCSM', 'CSMController@destroy')->name('deleteCSM');

    Route::group(['prefix' => 'alert-controller'], function () {
        Route::get('help-list', 'AdminAlertController@helpList')->name('alert.help.list');

        Route::get('list', 'AdminAlertController@index')->name('alert.list');

        Route::get('{id}/edit', 'AdminAlertController@editTask')->name('alert.edit');
        Route::put('update-task/{id}', 'AdminAlertController@updateTask')->name('alert.update');

        Route::get('create-alert', 'AdminAlertController@create')->name('alert.create');
        Route::post('create-task', 'AdminAlertController@store')->name('alert.store');
    });

    /********************* system administrators users listing ******************/
});
