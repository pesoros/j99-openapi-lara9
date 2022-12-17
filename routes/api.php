<?php

use App\Http\Controllers\api\HomeController;
use App\Http\Controllers\api\AuthenticationController;
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
Route::prefix('authentication')->group(function () {
    Route::controller(AuthenticationController::class)->group(function () {
        Route::post('register', 'register');
        Route::post('login', 'login');
    });
});

Route::middleware('auth:sanctum')->group(function () {
});
