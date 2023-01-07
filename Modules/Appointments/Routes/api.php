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

Route::middleware('auth:api')->get('/appointments', function (Request $request) {
    return $request->user();
});
Route::post('save-appointment-form', 'AppointFormController@AppointmentFormSettings')->name('save-appointment-form');

Route::post('save-payment-script', 'AppointmentSettingsController@AppointmentPaymentScript')->name('save-payment-script');

Route::get('get-appointment-form', 'AppointFormController@getAppointmentFormSettings')->name('get-appointment-form');

Route::post('add-appointment', 'AppointmentsController@createAppointment')->name('add-appointment');

Route::put('step-one-info', 'AppointmentsController@createAppointmentInfo')->name('step-one-info');

Route::post('step-two-info', 'AppointmentSettingsController@createUserInfo')->name('step-two-info');

Route::get('get-all-appointments', 'AppointmentsController@getAppointments')->name('get-all-appointments');

Route::get('get-appointment-detail', 'AppointmentsController@getAppointmentDetail')->name('get-appointment-detail');

Route::group(['middleware' => ['jwt.verify']], function() {

    Route::post('save-appointment-form', 'AppointmentSettingsController@AddAppointmentForm')->name('save-appointment-form');

    Route::post('delete-appointment-type', 'AppointmentSettingsController@RemoveAppointment')->name('delete-appointment-type');

    Route::get('get-appointments-location', 'AppointmentSettingsController@GetAppointmentLocation')->name('get-appointment-location');

    Route::get('get-appointments-category', 'AppointmentSettingsController@GetAppointmentCategories')->name('get-appointment-category');

    Route::get('get-appointments-services', 'AppointmentSettingsController@GetAppointmentServices')->name('get-appointment-services');

    Route::get('get-appointments-dates', 'AppointmentSettingsController@GetAppointmentDates')->name('get-appointment-dates');

});
