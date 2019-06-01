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

Auth::routes();

Route::get('/', 'AdminController@index')->middleware(\App\Http\Middleware\CheckAdmin::class);
Route::get('/achievement{id}/reject', 'AdminController@reject')->middleware(\App\Http\Middleware\CheckAdmin::class);
Route::get('/achievement{id}/confirm', 'AdminController@confirm')->middleware(\App\Http\Middleware\CheckAdmin::class);

Route::get('/user', 'UserController@index')->middleware(\App\Http\Middleware\CheckUser::class);
Route::get('/add_achievement', 'UserController@addView')->middleware(\App\Http\Middleware\CheckUser::class);
Route::post('/add_achievement', 'UserController@addAchievement')->middleware(\App\Http\Middleware\CheckUser::class);
Route::get('/achievement/{id}/send', 'UserController@send')->middleware(\App\Http\Middleware\CheckUser::class);
Route::get('/achievement/{id}/delete', 'UserController@delete')->middleware(\App\Http\Middleware\CheckUser::class);
Route::get('/achievement/{id}/edit', 'UserController@editView')->middleware(\App\Http\Middleware\CheckUser::class);
Route::post('/achievement/{id}/edit', 'UserController@edit')->middleware(\App\Http\Middleware\CheckUser::class);
