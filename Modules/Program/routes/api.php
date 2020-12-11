<?php

Route::resource('programs', 'ProgramController');

/* --------------- Program Domains --------------- */
Route::resource('programs/{id}/domains', 'DomainController');


/* --------------- Program Points --------------- */
Route::resource('programs/{id}/points', 'PointController', [ 'except' => 'show' ]);
Route::get('programs/{id}/points/current', 'PointController@currentBalance');

/* --------------- Users Points --------------- */
Route::resource('programs/{id}/users/{user_id}/points', 'PointController', [ 'except' => 'show' ]);
Route::get('programs/{id}/users/{user_id}/points/current', 'PointController@currentBalance');

/* --------------- Program Points Settings --------------- */
Route::get('programs/{id}/points-expiries', 'PointExpiresController@index');
Route::post('programs/{id}/points-expiries', 'PointExpiresController@handleExpiration');


/* --------------- Program Points Settings --------------- */
Route::put('programs/{id}/points_per_currency', 'PointSettingsController@updatePointsPerCurrencyValue');
Route::put('programs/{id}/country_currency_rate', 'PointSettingsController@updateCountryCourrencyRate');
Route::put('programs/{id}/country_currency_rate', 'PointSettingsController@updateBudgetStatus');


/* --------------- Program Points Budget --------------- */
Route::get('programs/{id}/budget', 'PointsBudgetController@index');
Route::put('programs/{id}/budget', 'PointsBudgetController@update');
Route::put('programs/{id}/budget-status', 'PointsBudgetController@changeBudgetStatus');

Route::get('currencies', 'CurrencyController@index');
Route::post('program/users/searchusers', 'ProgramController@search');

Route::get('allPointsHistory', 'PointController@pointsHistoryListing');
Route::post('program/add_voucher', 'ProgramController@addVoucher');
Route::get('program/vouchers_list', 'ProgramController@getVouchers');
Route::post('program/vouchers_status', 'ProgramController@updateVoucherStatus');
Route::post('program/update_voucher/{id}', 'ProgramController@updateVoucherDetails');
Route::post('programs/redeem_voucher', 'ProgramController@redeemVoucher');
Route::get('programs/get_voucher_users/{id}', 'ProgramController@getVoucherUsers');

Route::get('birthday_campaign', 'PointController@birthdayPoints');
Route::post('ecard_create', 'ProgramController@createEcards');
Route::get('get_ecards', 'ProgramController@getEcards');
Route::get('get_ecard_details/{id}', 'ProgramController@getCardDetails');
Route::post('ecard_update_info/{id}', 'ProgramController@updateEcardDetails');
Route::post('send_ecard', 'ProgramController@sendEcard');
Route::post('program/ecard_status', 'ProgramController@updateEcardStatus');
