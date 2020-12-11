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

Route::middleware('auth:api')->get('/commonsetting', function (Request $request) {
    return $request->user();
});

Route::get('get_currencies', 'CommonSettingController@getCurrencies');
Route::post('save_points/{id?}', 'CommonSettingController@saveCurrencyPoints');
Route::post('save_points_status', 'CommonSettingController@saveCurrencyPointsStatus');
Route::get('get_currency_points', 'CommonSettingController@getCurrenciesPoints');
Route::post('currency_calculation', 'CommonSettingController@getCurrenciesCalculations');
Route::post('default_currency', 'CommonSettingController@makeDefaultCurrency');
Route::get('save_grp_role/{group_id?}/{role_id?}', 'CommonSettingController@saveGroupList');