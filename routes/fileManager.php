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

Route::middleware(['web', 'auth:api', 'StorageAccessValidator'])->prefix('api/storage/')->group(function () {
    Route::get('/view/{attachmentId}', '\Yuyu\FileManager\Controllers\FileManagerController@viewFile');
    Route::get('/download/{attachmentId}', '\Yuyu\FileManager\Controllers\FileManagerController@downloadFile');
});
