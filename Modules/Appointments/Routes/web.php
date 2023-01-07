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

Route::prefix('appointments')->group(function() {
    Route::get('/', 'AppointmentsController@index');
});
Route::get('appointmentForm', 'AppointmentsController@displayForm')->name('appointmentForm');

Route::get('appointmentPage', 'AppointmentSettingsController@appointmentPage')->name('appointmentPage');

Route::get('appointmentServices', 'AppointmentSettingsController@getFormServices')->name('appointmentServices');
Route::get('appointmentProviders', 'AppointmentSettingsController@getFormProviders')->name('appointmentProviders');
Route::get('appointmentDates', 'AppointmentSettingsController@getFormAppointments')->name('appointmentDates');
Route::get('appointmentScript', 'AppointmentSettingsController@getFormAppointmentScript')->name('appointmentScript');
