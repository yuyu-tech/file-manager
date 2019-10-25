<?php

/*
|--------------------------------------------------------------------------
| storage Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['guest', 'StorageAccessValidator'])->prefix('storage/')->group(function(){
	Route::get('/view/{attachmentId}', '\Yuyu\FileManager\Controllers\FileManagerController@viewFile');
	Route::get('/download/{attachmentId}', '\Yuyu\FileManager\Controllers\FileManagerController@downloadFile');
});