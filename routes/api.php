<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\api\UserController;
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

Route::prefix('/users')->group(function () {

    Route::post('/register', 'api\AuthController@register')->name('auth.register');
    Route::post('/verify-account', 'api\EmailVerificationTokenController@verifyAccount')->name('users.verifyAccount');
    Route::get('/{id}/send-verification-code', 'api\EmailVerificationTokenController@sendVerificationCode')->name('users.sendVerificationCode');
    Route::post('/login', 'api\AuthController@login')->name('auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', 'api\AuthController@logout')->name('auth.logout');
        Route::post('/edit', 'api\UserController@update')->name('users.update');
        Route::post('/notifications', 'api\UserController@getUnreadNotifications');
        Route::post('/{id}/check-activity', 'api\UserController@getLastActivityTime');
    });
});

Route::middleware('auth:sanctum')->prefix('/admins')->group(function () {
    Route::get('/posts/unapproved', 'api\PostController@indexUnapproved');
    Route::get('/posts/{id}/approve', 'api\PostController@approvePost'); // admin accepts
    Route::post('/posts/{id}/unapprove', 'api\PostController@unapprovePost'); // admin rejects
    Route::get('/users', 'api\UserController@index');
    Route::get('/users/{id}/posts', 'api\PostController@showFromUser'); // shows posts of a specific user
    Route::get('/users/{id}/login', 'api\AuthController@loginInsteadOf'); // admin logs in in place of an other user
});
Route::middleware('auth:sanctum')->prefix('/posts')->group(function () {
    Route::get('/', 'api\PostController@index');
    Route::post('/create', 'api\PostController@create');
    Route::get('/{id}', 'api\PostController@show');
    Route::get('{id}/like', 'api\PostController@like');
    Route::post('{id}/comment', 'api\PostController@comment');
    Route::post('{id}/comment-like', 'api\PostController@commentAndLike');
    Route::get('/search/{query}', 'api\PostController@search');
    Route::get('/search/{query}/{orderBy}', 'api\PostController@searchAndOrderBy');
});

Route::prefix('/status')->group(function () { // Services Health checking
    Route::post('/database', 'api\ServiceStatusController@checkDatabaseHealth');
    Route::middleware('auth:sanctum')->post('/mail', 'api\ServiceStatusController@checkMailHealth');
    Route::middleware('auth:sanctum')->post('/redis', 'api\ServiceStatusController@checkRedisHealth');
});
