<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/**
 * Routes for the admin operations
 */
Route::group(['prefix' => 'admin', 'namespace' => 'Admin'], function () {
    Route::post('users', 'UserController@store');
    Route::put('users/password/{userByToken}', 'UserController@password');
});