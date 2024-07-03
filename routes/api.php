<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\CountriesController;
use App\Http\Controllers\Api\CmsController;
// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('signup', [AuthController::class, 'signup']);
Route::post('login', [AuthController::class, 'login']);
Route::post('username_checks', [AuthController::class, 'username_checkups']);
Route::post('email_checks', [AuthController::class, 'email_checkups']);
Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('user', [AuthController::class, 'user']);
Route::get('search', [SearchController::class, 'search']);
Route::get('getAllCountries', [CountriesController::class, 'getAllCountries']);
Route::get('getcmsdata', [CmsController::class, 'getcmsdata']);
