<?php

use App\Http\Controllers\api\HomeController;
use App\Http\Controllers\api\AuthenticationController;
use App\Http\Controllers\api\MasterDataController;
use Illuminate\Support\Facades\Route;

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

Route::get('', [HomeController::class, 'index']);
Route::get('master/province', [MasterDataController::class, 'province']);
Route::prefix('authentication')->group(function () {
    Route::controller(AuthenticationController::class)->group(function () {
        Route::post('register', 'register');
        Route::post('login', 'login');
    });
});

Route::prefix('master')->group(function () {
    Route::controller(MasterDataController::class)->group(function () {
        Route::get('province', 'province');
        Route::get('city', 'city');
        Route::get('city/{province_id}', 'city', );
    });
});
Route::middleware('auth:sanctum')->group(function () {

    
});
