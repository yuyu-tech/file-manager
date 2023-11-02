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

Route::middleware(['guest', 'StorageAccessValidator'])->prefix('guest/storage/')->group(function () {
    Route::get('/view/{attachmentId}', '\Yuyu\FileManager\Controllers\FileManagerController@viewFile')->name('guest.view.file');
    Route::get('/download/{attachmentId}', '\Yuyu\FileManager\Controllers\FileManagerController@downloadFile')->name('guest.download.file');
});

Route::middleware(['web', 'auth', 'StorageAccessValidator'])->prefix('web/storage/')->group(function () {
    Route::get('/view/{attachmentId}', '\Yuyu\FileManager\Controllers\FileManagerController@viewFile')->name('web.view.file');
    Route::get('/download/{attachmentId}', '\Yuyu\FileManager\Controllers\FileManagerController@downloadFile')->name('web.download.file');
});
$guards = array_keys(config("auth.guards", []));
$guards = array_filter($guards, function($guard) {
    return $guard !== 'web';
});
$guards = implode(',', $guards);

Route::middleware(["auth:{$guards}", 'StorageAccessValidator'])->prefix('api/storage/')->group(function () {
    Route::get('/view/{attachmentId}', '\Yuyu\FileManager\Controllers\FileManagerController@viewFile')->name('api.view.file');;
    Route::get('/download/{attachmentId}', '\Yuyu\FileManager\Controllers\FileManagerController@downloadFile')->name('api.download.file');
});
