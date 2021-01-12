<?php

/*
|--------------------------------------------------------------------------
| Web routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/export-file/{program_id}/{file}', function ($program_id, $file) {
    return response()->download(storage_path('app/uploaded/'.$program_id.'/users/csv/exported/'.$file), $file);
});

Route::get('/newImage/{image}/{message}', function ($image, $message) {
    return view('newImage', ["image"=>$image,"message"=>$message]);
});
