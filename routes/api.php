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

Route::group(['namespace' => 'api\\'], function () {


    Route::group(['prefix' => '/users',], function () {
        Route::post('/register', 'AuthController@register')->name('auth.register');
        Route::post('/verify-account', 'EmailVerificationTokenController@verifyAccount')->name('users.verifyAccount');
        Route::get('/{id}/send-verification-code', 'EmailVerificationTokenController@sendVerificationCode')->name('users.sendVerificationCode');
        Route::post('/login', 'AuthController@login')->name('auth.login');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', 'AuthController@logout')->name('auth.logout');
            Route::post('/edit', 'UserController@update')->name('users.update');
            Route::post('/notifications', 'UserController@getUnreadNotifications');
            Route::post('/{id}/check-activity', 'UserController@getLastActivityTime');
        });
    });

    Route::middleware('auth:sanctum')->prefix('/admins')->group(function () {
        Route::get('/posts/unapproved', 'PostController@indexUnapproved');
        Route::get('/posts/{id}/approve', 'PostController@approvePost'); // admin accepts
        Route::post('/posts/{id}/unapprove', 'PostController@unapprovePost'); // admin rejects
        Route::get('/users', 'UserController@index');
        Route::get('/users/{id}/posts', 'PostController@showFromUser'); // shows posts of a specific user
        Route::get('/users/{id}/login', 'AuthController@loginInsteadOf'); // admin logs in in place of an other user
    });
    Route::middleware('auth:sanctum')->prefix('/posts')->group(function () {
        Route::get('/', 'PostController@index');
        Route::post('/create', 'PostController@create');
        Route::get('/{id}', 'PostController@show');
        Route::get('{id}/like', 'PostController@like');
        Route::post('{id}/comment', 'PostController@comment');
        Route::post('{id}/comment-like', 'PostController@commentAndLike');
        Route::get('/search/{query}', 'PostController@search');
        Route::get('/search/{query}/{orderBy}', 'PostController@searchAndOrderBy');
    });

    Route::prefix('/status')->group(function () { // Services Health checking
        Route::post('/database', 'ServiceStatusController@checkDatabaseHealth');
        Route::middleware('auth:sanctum')->post('/mail', 'ServiceStatusController@checkMailHealth');
        Route::middleware('auth:sanctum')->post('/redis', 'ServiceStatusController@checkRedisHealth');
    });
});
