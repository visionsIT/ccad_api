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

/**************Overall Report**************/
Route::get('user_groups/{accountid}','CommonSettingController@userGroups');
Route::post('overall_report','CommonSettingController@overallReport');
Route::post('download_overall_registrations', 'CommonSettingController@downloadOverallRegistrations');

/**************Recognition Report***********/
Route::post('recognition_report','CommonSettingController@recognitionReport');
Route::get('login_visit', 'CommonSettingController@loginVisit');
Route::post('login_visit_csv', 'CommonSettingController@loginVisitCsv');
Route::post('active-user-csv', 'CommonSettingController@activeInactive');
Route::get('campaign_list','CommonSettingController@getCampaignList');
Route::post('nomination_point_csv', 'CommonSettingController@nominationPointsCsv');
Route::post('award_cost_csv', 'CommonSettingController@awardCostCsv');
Route::post('rewards_report','CommonSettingController@rewardsReport');
Route::post('product_report_csv','CommonSettingController@productReportCsv');
Route::post('product_overall_report','CommonSettingController@productOverallReport');
Route::post('popular_reward_csv','CommonSettingController@popularRewardCsv');
/***********Api's for email templates************/
Route::get('email_template_types','EmailTemplatesController@emailTemplateTypes');
Route::get('email_templates','EmailTemplatesController@emailTemplates');
Route::post('emailSettings/status','EmailTemplatesController@emailTemplateStatusChange');


Route::post('emailSettings/saveEmailTemplateContent', 'EmailTemplatesController@saveDynamicEmailContent');
Route::get('emailSettings/getEmailTemplate/{template_id}', 'EmailTemplatesController@getEmailTemplateByID');