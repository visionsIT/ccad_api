<?php

use Illuminate\Http\Request;
use \App\Helpers;
/*
|--------------------------------------------------------------------------
| API routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', 'AuthController@login');
  
    Route::group([
      'middleware' => 'auth:api'
    ], function() {
		
		if (isset($_SERVER) && !empty($_SERVER) && isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
			Helper::ValidateGetRequestParameters();
		}

		Route::get('logout', 'AuthController@logout');
    });
});