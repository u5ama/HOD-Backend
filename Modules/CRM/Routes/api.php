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

Route::middleware('auth:api')->get('/crm', function (Request $request) {
    return $request->user();
});

Route::post('save-custom-form', 'CRMController@CustomerFormSettings')->name('save-custom-form');
Route::get('get-custom-form', 'CRMController@getCustomerFormSettings')->name('get-custom-form');

Route::post('delete-form-field', 'CRMController@deleteField')->name('delete-form-field');
Route::post('delete-new-form-field', 'CRMController@deleteCustomField')->name('delete-new-form-field');
Route::get('get-deleted-fields', 'CRMController@getDeleteFields')->name('get-deleted-fields');

Route::post('add-form-field', 'CRMController@addField')->name('add-form-field');
Route::get('get-form-field', 'CRMController@getAddField')->name('get-form-field');

Route::post('crm-add-customer', 'CRMController@addCustomer')->name('crm-add-customer');

Route::group(['middleware' => ['jwt.verify']], function() {

    Route::get('old-get-more-reviews', 'CRMController@customersList')->name('crm-customers');

    Route::get('customers', 'CRMController@customersList')->name('customers');

    Route::get('get-more-reviews', 'CRMController@addPatient')->name('add-patient');

    Route::post('search-customer', 'CRMController@searchCustomers')->name('search-customer');

    Route::get('crm-delete-customer', 'CRMController@deleteCustomer')->name('crm-delete-customer');

    Route::post('crm-update-customer', 'CRMController@updateCustomer')->name('crm-update-customer');

    Route::post('crm-upload-customer-csv', 'CRMController@uploadCustomersCSV')->name('crm-upload-customer-csv');

    Route::post('crm-upload-customer-file', 'CRMController@uploadCustomersFile')->name('crm-upload-customer-file');

    Route::post('crm-background-service', 'CRMController@CRMBackgroundService')->name('crm-background-service');

    Route::get('crm-customers-settings', 'CRMController@crmCustomersSettings')->name('crm-customers-settings');

    Route::get('crm-get-customer', 'CRMController@singleCustomerData')->name('crm-get-customer');

    Route::post('crm-customers-list', 'CRMController@getCRMCustomersList')->name('crm-customers-list');

    Route::post('requests-sent', 'CRMController@showRecipientList')->name('reviews-recipients');

    Route::post('emailPersonalizeDesign', 'CRMController@emailPersonalizeDesign')->name('emailPersonalizeDesign');

    Route::post('emailSentUser', 'CRMController@emailSentUser')->name('emailSentUser');

    Route::post('personalizeTouch', 'CRMController@personalizeTouch')->name('personalizeTouch');

    Route::post('emailNegativeAnswerSetup', 'CRMController@emailNegativeAnswerSetup')->name('emailNegativeAnswerSetup');

    Route::get('email', 'CRMController@emailSettings')->name('email');

    Route::get('sms', 'CRMController@smsView')->name('sms');

    Route::post('smsImage', 'CRMController@smsImage')->name('smsImage');

    Route::post('smsMessage', 'CRMController@smsMessage')->name('smsMessage');

    Route::get('get-all-countries', 'CRMController@AllCountries')->name('get-all-countries');

    Route::get('get-all-leads', 'CRMController@customerLeads')->name('get-all-leads');

    Route::get('get-all-overviews', 'CRMController@getCustomersOverview')->name('get-all-overviews');
});
