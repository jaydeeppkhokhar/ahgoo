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
Route::post('country_of_origin', [SearchController::class, 'country_of_origin']);
Route::post('influencers', [SearchController::class, 'influencers']);
Route::post('follow', [ProfileController::class, 'follow']);
Route::post('unfollow', [ProfileController::class, 'unfollow']);
Route::post('sent_friend_request', [ProfileController::class, 'sent_friend_request']);
Route::post('block_user', [ProfileController::class, 'block_user']);
Route::post('report_user', [ProfileController::class, 'report_user']);
Route::post('my_profiles', [ProfileController::class, 'my_profiles']);
Route::post('user_profiles', [ProfileController::class, 'user_profiles']);
Route::post('my_followers', [ProfileController::class, 'my_followers']);
Route::post('followings', [ProfileController::class, 'followings']);
Route::post('my_freinds', [ProfileController::class, 'my_freinds']);
Route::post('notifications', [ProfileController::class, 'notifications']);
Route::post('see_notifications', [ProfileController::class, 'see_notifications']);
Route::post('profile_step_one', [ProfileController::class, 'profile_step_one']);
Route::post('profile_step_two', [ProfileController::class, 'profile_step_two']);
Route::post('profile_step_three', [ProfileController::class, 'profile_step_three']);
Route::post('profile_step_four', [ProfileController::class, 'profile_step_four']);
Route::get('hobbies', [SearchController::class, 'hobbies']);
Route::get('influencer_categories', [SearchController::class, 'influencer_categories']);
Route::post('influencer_category_map', [ProfileController::class, 'influencer_category_map']);
Route::post('influencer_acceptance', [ProfileController::class, 'influencer_acceptance']);
Route::post('update_location', [ProfileController::class, 'update_location']);
Route::post('create_post', [ProfileController::class, 'create_post']);
Route::post('home_page', [ProfileController::class, 'home_page']);
Route::get('all_locations', [SearchController::class, 'all_locations']);
Route::post('create_promotion_1', [ProfileController::class, 'create_promotion_1']);
Route::post('create_promotion_2', [ProfileController::class, 'create_promotion_2']);
Route::post('create_promotion_audience', [ProfileController::class, 'create_promotion_audience']);
Route::post('create_promotion_budget', [ProfileController::class, 'create_promotion_budget']);
Route::post('create_promotion_confirm', [ProfileController::class, 'create_promotion_confirm']);
Route::post('audience_name_check', [ProfileController::class, 'audience_name_check']);
Route::post('delete_promotion', [ProfileController::class, 'delete_promotion']);
Route::post('my_posts', [ProfileController::class, 'my_posts']);
Route::post('create_post_thumbnail', [ProfileController::class, 'create_post_thumbnail']);
Route::post('home_search_for_you', [ProfileController::class, 'home_search_for_you']);
Route::post('home_search_accounts', [ProfileController::class, 'home_search_accounts']);
Route::post('home_search_videos', [ProfileController::class, 'home_search_videos']);
Route::post('latest_profile_views', [ProfileController::class, 'latest_profile_views']);
Route::post('latest_keyword_search', [ProfileController::class, 'latest_keyword_search']);
Route::post('like_post', [ProfileController::class, 'like_post']);
Route::post('dislike_post', [ProfileController::class, 'dislike_post']);
Route::post('event_background_videos', [ProfileController::class, 'event_background_videos']);
Route::post('recent_events', [ProfileController::class, 'recent_events']);
Route::post('upload_background_videos', [ProfileController::class, 'upload_background_videos']);
Route::post('event_suggesstion_posts', [ProfileController::class, 'event_suggesstion_posts']);
Route::post('recent_events_your_countries', [ProfileController::class, 'recent_events_your_countries']);
Route::post('recent_events_near_you', [ProfileController::class, 'recent_events_near_you']);
Route::post('event_details', [ProfileController::class, 'event_details']);
Route::post('event_confirm', [ProfileController::class, 'event_confirm']);
Route::post('upload_flag_image', [ProfileController::class, 'upload_flag_image']);
