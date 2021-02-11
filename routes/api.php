<?php

use App\Http\Controllers\Api\Product\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\LoginController;

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

Route::group(['as' => 'api.'], function(){
    Route::post('/login', [LoginController::class,'authenticate'])->name('login');

    Route::group(['middleware' => ['jwt.auth']], function(){
        Route::resource('products', ProductController::class)->except(['create','edit']);
    });
});

