<?php

Route::put('accounts/{id}', 'AccountController@update');

Route::post('saml2/ccad/acs', 'AccountController@getSSOAuthenticated');
Route::get('authenticated-account/data', 'AccountController@getAuthenticatedAccountData');

Route::post('authenticated-account/badges', 'AccountController@getAuthenticatedAccountBudges');

Route::post('give/{account}/permissions', 'AccountController@syncPermissions');
Route::resource('permissions', 'PermissionController');
Route::resource('roles', 'RoleController');
Route::get('/getRolesByProgram/{id}', 'RoleController@index');
Route::post('role/change_status', 'RoleController@updateRoleStatus');
Route::post('role/update_info/{id}', 'RoleController@updateRoleInfo');

Route::post('permissions/search', 'PermissionController@search');
Route::post('roles/change_permission', 'RoleController@changePermission');

Route::get('get/role/users/{id}', 'RoleController@getRoleAccounts');
Route::get('get/permission/users/{id}', 'PermissionController@getPermissionAccounts');

Route::get('role/{id}/permissions', 'RoleController@getRolePermissions');
Route::post('assignPermissionsToGroup', 'RoleController@assignPermissionsToGroup');
Route::get('get/permissions/claim_award_permission', 'PermissionController@getPermissionForClaimAwards');
Route::post('permissions/change_claimAward_permission', 'PermissionController@updateClaimAwardPermisssion');
Route::post('permissions/update_campaign_msg', 'RoleController@updateCampaignMessage');
Route::post('permissions/global_birthday_campaign_permission', 'PermissionController@changeBirthdayPermissionsGlobal');

Route::post('change_ecards_permission', 'PermissionController@ecardPermission');
Route::post('change_permission_status', 'PermissionController@changePermissionStatus');

{
    /*  Passwords routes */
    Route::post('password/reset', [ 'uses' => 'PasswordsController@resetPassword', 'as' => 'password.reset' ]); // TODO  'middleware' => 'throttle:5,1',
    Route::post('password/reset/{token}', [ 'uses' => 'PasswordsController@confirmResetPassword', 'as' => 'confirmResetPassword' ]);
    Route::post('password/create', [ 'uses' => 'PasswordsController@createNewPassword', 'as' => 'createNewPassword' ]);
    Route::post('password/change/{account_id}', [ 'uses' => 'PasswordsController@changeOldPassword', 'as' => 'changeOldPassword' ]); // need auth middleware
}
