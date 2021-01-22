<?php
Route::get('user/user_percentage', 'UserController@feedPieChart');
Route::get('users/{user_id}', 'UserController@show');
Route::resource('program/{program}/users', 'UserController');
Route::get('program/all_users/{campaign_id?}', 'UserController@getAllUsers');
Route::get('v2/program/{program}/users', 'UserController@paginatedUsers');
Route::post('program/users/search', 'UserController@search');
Route::post('assign/user/to/group', 'UserController@assignUserToGroup');
Route::post('group_assignment','UserController@groupAssignmentUser');
Route::get('group_user_list/{role_id}/{group_id}','UserController@groupUsersList');
Route::get('group_exclude_users/{group_id?}','UserController@groupExcludeUsersList');
Route::get('get_user_roles', 'UserManageController@getUserRoles');
Route::post('program/users/import', 'UserManageController@import');
Route::get('program/users/download/{program_id}', 'UserManageController@download');
Route::get('program/users/export/{program_id}', 'UserManageController@export');

/* --------------- users Points --------------- */
Route::resource('programs/{id}/users/{user_id}/points', 'PointController', [ 'except' => 'show' ]);
Route::get('programs/{id}/users/{user_id}/points/current', 'PointController@currentBalance');
Route::post('users/{user_id}/points/add', 'PointController@addPointsToSpecificUser');

// User_Goal_Item
Route::post('users/{user_id}/goal_item', 'GoalItemController@store');
Route::get('users/{user_id}/goal_item', 'GoalItemController@getUserGoalItem');
Route::post('users/{user_id}/goal_item_remove', 'GoalItemController@removeGoalItem');

//Departments
Route::get('departments', 'DepartmentController@index');
//Teams
Route::get('teams', 'TeamController@index');
Route::post('team/{team_id}', 'TeamController@update');
Route::get('team/{team_id}', 'TeamController@destroy');
Route::get('teams/get-user-teams/{account_id}', 'TeamController@getUserTeams');
Route::post('user/get-all-employees', 'UserController@getAllEmployees');
Route::get('user/get-all-employees', 'UserController@getAllEmployees');
Route::resource('team', 'TeamController');
Route::middleware('auth:api')->get('user/test', 'UserController@test');

//Feedback
Route::post('user/add_feedback', 'TeamController@newFeedback');

//Cron job to get employees data with API
Route::get('program/users/importemployee', 'UserManageController@importEmployeeApi');
Route::get('program/{program}/role_users/{role_id}', 'UserController@getUsersRoleWise');
Route::post('user/change_status', 'UserController@updateUserStatus');
Route::post('user/change_gu_status','UserController@changeGroupUserStatus');
Route::get('user/{user_id}', 'UserController@show');
Route::post('user/updateinfo/{user_id}', 'UserController@updateUserInfo');
Route::get('user/points/{user_id}', 'UserController@getUserPoints');
Route::get('program/{program_id}/getdashboardinfo', 'UserManageController@getDashboardDetails');

//Route::post('filterPointsHistory', 'PointController@filterPointsHistory');
Route::get('filterPointsHistory', 'PointController@filterPointsHistory');
Route::post('user/upload_budget', 'UserManageController@uploadUsersBudget');
Route::post('user/add_user_budget/{id?}', 'UserManageController@addUserCampaignsBudget');
Route::get('list_user_budget/{campaign_id?}', 'UserManageController@listUserCampaignsBudget');

#####################################SSO_login_details#################################
Route::get('get_sso_login_details', 'UserController@getssoLoginDetails');
Route::post('user/save_sso_login_details', 'UserController@saveSsoLoginDetails');
Route::post('user/add_suggestion', 'UserManageController@AddUserSuggestion');
Route::get('get_group_leads/{group_id?}', 'UserController@getGroupLeadUsers');

##########Upload profile pic of user############
Route::post('/upload_profile_pic','UserController@uploadUserProfilePic');
Route::get('/get_profile_pic/{account_id}','UserController@getUserProfilePic');
