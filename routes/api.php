<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\CountriesController;
use App\Http\Controllers\Api\CmsController;
use App\Http\Controllers\Api\ProfileController;
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
Route::get('updateCountry', [AuthController::class, 'updateCountry']);
Route::post('forget_password', [AuthController::class, 'forget_password']);
Route::post('send_otp', [AuthController::class, 'send_otp']);
Route::post('verify_otp', [AuthController::class, 'verify_otp']);
Route::post('change_password', [AuthController::class, 'change_password']);
Route::post('suggestion', [SearchController::class, 'suggestions']);
Route::post('people_near_you', [SearchController::class, 'people_near_you']);
Route::post('follow', [ProfileController::class, 'follow']);
Route::post('my_profiles', [ProfileController::class, 'my_profiles']);
