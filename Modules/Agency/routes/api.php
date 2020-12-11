<?php

Route::resource('clients/{id}/admins', 'ClientAdminsController');
Route::resource('clients', 'ClientController');
Route::resource('catalogues', 'CatalogueController');

Route::resource('agencies', 'AgencyController');
Route::resource('agencies/{id}/admins', 'AgencyAdminsController');
Route::get('agencies/{id}/clients', 'ClientController@agencyClients');
Route::get('get_group_levels', 'GroupLevelsController@index');
Route::resource('add_group_level', 'GroupLevelsController');
Route::resource('static_pages', 'StaticPagesController');
Route::post('static_pages/change_status', 'StaticPagesController@updatePageStatus');
Route::post('uploader/upload', 'StaticPagesController@uploadImage');
Route::get('browser/browse', 'StaticPagesController@getImages');