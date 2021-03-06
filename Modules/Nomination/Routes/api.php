<?php

Route::resource('nominations', 'NominationController');
Route::resource('value_set', 'CampaignLevelController');

Route::post('update_value_set/{id}', 'CampaignLevelController@updateType');
Route::post('add_value_set', 'CampaignLevelController@addNewType');
Route::get('get_campaign_type_list', 'CampaignLevelController@campaignList');

Route::get('nomination/type/by/{value_set_id}', 'NominationTypeController@getNominationTypeBy');
Route::resource('nomination_type', 'NominationTypeController');
Route::post('nomination_type/update/{id}', 'NominationTypeController@updateTypeData');
Route::put('update/badge', 'NominationTypeController@updateBadges');
Route::get('badges/{account_id}', 'NominationTypeController@NominationBadges');
Route::get('nominations/{nomination_id}/values', 'NominationController@NominationValues');
//Route::get('nominations/{nomination_id}/wall', 'NominationController@NominationWall');
Route::get('nominations/campaign/wall/{campaign_id?}', 'NominationController@NominationCampaignWall');
Route::get('nomination/ecards_wall', 'NominationController@NominationEcardWall');
Route::get('badges/wall', 'NominationController@NominationBadgesWall');
Route::resource('nominations/{id}/decline', 'NominationDeclineController');
Route::resource('nominations/{id}/type', 'NominationTypeController');

Route::resource('user_nominations', 'UserNominationController');
Route::get('user_Ethankyou', 'UserNominationController@user_EthankyouRecords');
Route::post('send_nomination', 'UserNominationController@sendNomination');
Route::get('user_nomination/{id}/{account_id}/{status?}', 'UserNominationController@getUsersBy');
Route::get('user_l2_nomination/{id}/{account_id}/{status?}', 'UserNominationController@getL2NominatinsList');

Route::get('user_nominations/{nomination_id}/approved_level_one/{account_id}', 'UserNominationController@getApprovedUsersLevelOne');
Route::get('user_nominations/{nomination_id}/approved_level_two/{account_id}', 'UserNominationController@getApprovedUsersLevelTwo');

Route::resource('nominations/{id}/awards_levels', 'AwardsLevelController');
Route::get('awards_levels/{id}', 'AwardsLevelController@show');
Route::post('awards_level_update/{id}', 'AwardsLevelController@updateAwardLevel');

Route::resource('nominations/{id}/approval', 'SetApprovalController');

Route::get('getNominationData/{id}', 'UserNominationController@getNominationData');

Route::get('icons', 'NominationController@NominationValuesIcons');
Route::get('icons2', 'NominationController@NominationValuesIcons2');

Route::put('update/level/one/{userNominationId}', 'UserNominationController@updateLevelOne');
Route::put('update/level/two/{userNominationId}', 'UserNominationController@updateLevelTwo');
Route::put('update/points/{userNominationId}', 'UserNominationController@updatePoints');

Route::get('testMail', 'UserNominationController@testMail');
Route::get('approvers/{id}', 'UserNominationController@list_first_approvers');

Route::get('nominations/{nomination_id}/users', 'NominationController@users');

Route::get('nomination/report', 'UserNominationController@report');
Route::get('nomination/getAllReport', 'UserNominationController@reportToGetAllNominationDetails');

Route::post('team_nominations/create', 'UserNominationController@teamNomination');
Route::get('team_nominations/pending-approvals/{id}', 'UserNominationController@pendingApprovals');
Route::put('team_nominations/approved', 'UserNominationController@approvedNomination');
Route::put('team_nominations/reject', 'UserNominationController@rejectNomination');

Route::get('nomination/export-report', 'UserNominationController@exportReport');
/* New Api for user nominations */
Route::get('user-nomination', 'UserNominationController@nominations');
Route::get('nomination/claim_types', 'UserNominationController@getClaimTypes');
Route::post('nomination/add_claim', 'UserNominationController@addClaim');
Route::get('user/user_claim_list/{id}', 'UserNominationController@getUserClaims');
Route::post('user/approve_claim', 'UserNominationController@approveClaim');
Route::post('user/decline_claim', 'UserNominationController@declineClaim');

Route::post('nomination_type/update_status', 'NominationTypeController@updateStatus');
Route::post('value_set/update_status', 'CampaignLevelController@updateStatus');
Route::post('award_levels/update_status', 'AwardsLevelController@updateStatus');



Route::post('save_ripple_settings', 'RippleSettingsController@saveRippleSettings');
Route::post('create_ecards_ripple', 'RippleSettingsController@createEcardsRipple');
Route::post('update_ecard_ripple', 'RippleSettingsController@updateEcardsRipple');
Route::get('ripple/by/{id}', 'RippleSettingsController@getRippleSettings');
Route::post('ecard_status_change', 'RippleSettingsController@ecardStatusChange');
Route::post('ecard_status_delete', 'RippleSettingsController@ecardStatusDelete');
Route::get('ripple/slug/{slug}', 'RippleSettingsController@getRippleSettingsBySlug');
Route::post('ripple_budget', 'RippleSettingsController@rippleBudgetByEmail');
Route::post('send_ecard_ripple', 'RippleSettingsController@sendEcardRipple');
Route::post('save_eligible_users_settings', 'RippleSettingsController@saveEligibleUsersSettings');


Route::get('get_nomination_type/{campaign_id}/{program_user_id}', 'CampaignLevelController@getNominationType');
Route::get('get_campaign_type', 'CampaignLevelController@getCampaignTypes');

Route::post('save_wall_settings', 'NominationController@saveWallSettings');
Route::get('get_wall_settings/{campaign_id}', 'NominationController@getWallSettings');
Route::post('user/campaign_report', 'UserNominationController@getCampaignReport');
Route::post('user/campaign_report_specific', 'UserNominationController@getCampaignReport_count');

Route::post('import_user_nominations','UserNominationController@importUserNominations');
Route::post('import_user_ecards','UserNominationController@importUserEcards');

Route::get('user/schedule-ecards','UserNominationController@getScheduledEcards');

Route::get('generateCertificateImage/', 'UserNominationController@generateCertificateImage');

// Campaign Record Export
Route::get('user_submission_export', 'CampaignQuestionsController@SubmissionRecordsExport');
Route::get('user_nomination_export', 'CampaignQuestionsController@NominationRecordsExport');
Route::get('user_sendEcard_export', 'CampaignQuestionsController@sendEcardRecordsExport');
Route::get('user_anniversary_export', 'CampaignQuestionsController@AnniversaryRecordsExport');

Route::get('get_campaignleads', 'NominationController@getCampaignLeadUsers');
Route::post('save_campaign_roles', 'NominationController@SaveCampaignRoles');
Route::post('delete_campaign_roles', 'NominationController@DeleteCampaignRoles');
Route::get('get_campaign_roles', 'NominationController@getCampaignRoles');
Route::get('get_campaign_roles_setting', 'NominationController@getUserCampaignRoleSettings');
Route::post('save_campaign_roles_setting', 'NominationController@saveUserCampaignRoleSettings');
Route::get('user_check_campaign/{account_id}', 'NominationController@checkUserInCampaignSetting');

Route::post('update_like_flag', 'NominationController@UpdateLikeFlag');
Route::post('add_comment', 'NominationController@AddComment');
