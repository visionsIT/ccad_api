<?php

Route::get('view/program/{program}/access_type', 'AccessTypeController@show');
Route::put('access_type/{access_type}', 'AccessTypeController@update');


Route::get('view/program/{program}/form', 'RegistrationFormController@show');
Route::put('form/{registration_form}', 'RegistrationFormController@update');
