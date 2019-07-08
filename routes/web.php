<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::middleware('auth')->group(function () {
	Route::get('/files', 'FileController@index');
	Route::get('/files/download/{id}', 'FileController@download');
	Route::post('/files', 'FileController@store');
	Route::put('/files/{id}', 'FileController@update');
	Route::delete('/files/{id}', 'FileController@destroy');
});
