<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\CountriesController;
use App\Http\Controllers\Api\CmsController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\AgoraTokenController;
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
Route::get('eventCategories', [SearchController::class, 'eventCategories']);
Route::get('influencer_categories', [SearchController::class, 'influencer_categories']);
Route::post('influencer_category_map', [ProfileController::class, 'influencer_category_map']);
Route::post('influencer_acceptance', [ProfileController::class, 'influencer_acceptance']);
Route::post('update_location', [ProfileController::class, 'update_location']);
Route::post('create_post', [ProfileController::class, 'create_post']);
Route::post('home_page', [ProfileController::class, 'home_page']);
Route::get('all_locations', [SearchController::class, 'all_locations']);
Route::get('all_cities', [SearchController::class, 'all_cities']);
Route::post('locations_search', [SearchController::class, 'locations_search']);
Route::post('create_promotion_1', [ProfileController::class, 'create_promotion_1']);
Route::post('create_promotion_2', [ProfileController::class, 'create_promotion_2']);
Route::post('create_promotion_audience', [ProfileController::class, 'create_promotion_audience']);
Route::post('create_promotion_budget', [ProfileController::class, 'create_promotion_budget']);
Route::post('create_promotion_payment_method', [ProfileController::class, 'create_promotion_payment_method']);
Route::post('create_promotion_confirm', [ProfileController::class, 'create_promotion_confirm']);
Route::post('audience_name_check', [ProfileController::class, 'audience_name_check']);
Route::post('delete_promotion', [ProfileController::class, 'delete_promotion']);
Route::post('promotion_details', [ProfileController::class, 'promotion_details']);
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
Route::post('bookmark_event', [ProfileController::class, 'bookmark_event']);
Route::post('undo_bookmark_event', [ProfileController::class, 'undo_bookmark_event']);
Route::post('get_bookmark_event', [ProfileController::class, 'get_bookmark_event']);
Route::post('event_confirm', [ProfileController::class, 'event_confirm']);
Route::post('upload_flag_image', [ProfileController::class, 'upload_flag_image']);
Route::post('friend_request_list', [ProfileController::class, 'friend_request_list']);
Route::post('pending_friend_request_list', [ProfileController::class, 'pending_friend_request_list']);
Route::post('accept_friend_request', [ProfileController::class, 'accept_friend_request']);
Route::post('reject_friend_request', [ProfileController::class, 'reject_friend_request']);
Route::post('delete_friend_request', [ProfileController::class, 'delete_friend_request']);
Route::post('notification_count', [ProfileController::class, 'notification_count']);
Route::post('generate-agora-token', [AgoraTokenController::class, 'generateToken']);
Route::post('update_profile_summary', [ProfileController::class, 'update_profile_summary']);
Route::post('switch_profile', [ProfileController::class, 'switch_profile']);
Route::post('uploadEventImages', [ProfileController::class, 'uploadEventImages']);
Route::post('uploadEventVideo', [ProfileController::class, 'uploadEventVideo']);
Route::post('create_event_1', [ProfileController::class, 'create_event_1']);
Route::post('create_event_2', [ProfileController::class, 'create_event_2']);
Route::post('create_event_budget', [ProfileController::class, 'create_event_budget']);
Route::post('create_events_confirm', [ProfileController::class, 'create_events_confirm']);
Route::post('uploadEventCoverImage', [ProfileController::class, 'uploadEventCoverImage']);
Route::post('update_profile_details', [ProfileController::class, 'update_profile_details']);
Route::post('create_event_type', [ProfileController::class, 'create_event_type']);
Route::post('create_event_slide_2', [ProfileController::class, 'create_event_slide_2']);
Route::post('create_event_slide_4', [ProfileController::class, 'create_event_slide_4']);
Route::post('event_name_checking', [ProfileController::class, 'event_name_checking']);
Route::post('event_subtitle_checking', [ProfileController::class, 'event_subtitle_checking']);
Route::post('replaceEventImage', [ProfileController::class, 'replaceEventImage']);
Route::post('my_event_followers', [ProfileController::class, 'my_event_followers']);
Route::post('my_event_followings', [ProfileController::class, 'my_event_followings']);
Route::post('my_event_friends', [ProfileController::class, 'my_event_friends']);
Route::post('my_event_all_inv', [ProfileController::class, 'my_event_all_inv']);
Route::post('sent_event_invite', [ProfileController::class, 'sent_event_invite']);
Route::post('event_all_details', [ProfileController::class, 'event_all_details']);
Route::post('event_edit_information', [ProfileController::class, 'event_edit_information']);
Route::post('delete_event', [ProfileController::class, 'delete_event']);
Route::post('paid_events_slide_4', [ProfileController::class, 'paid_events_slide_4']);
Route::post('paid_event_create_audience', [ProfileController::class, 'paid_event_create_audience']);
Route::post('paid_event_audience_name_check', [ProfileController::class, 'paid_event_audience_name_check']);
Route::post('paid_events_about_the_atendees', [ProfileController::class, 'paid_events_about_the_atendees']);
Route::post('paid_events_add_web_address', [ProfileController::class, 'paid_events_add_web_address']);
Route::post('paid_event_budget', [ProfileController::class, 'paid_event_budget']);
Route::post('paid_event_payment_method', [ProfileController::class, 'paid_event_payment_method']);
Route::post('categories_event_wise', [ProfileController::class, 'categories_event_wise']);
Route::post('events_by_category', [ProfileController::class, 'events_by_category']);
Route::post('my_active_events', [ProfileController::class, 'my_active_events']);
Route::post('my_finished_events', [ProfileController::class, 'my_finished_events']);
Route::post('create_promotion_target', [ProfileController::class, 'create_promotion_target']);
Route::post('update_preferred_countries', [ProfileController::class, 'update_preferred_countries']);
Route::post('update_preferred_interest', [ProfileController::class, 'update_preferred_interest']);
Route::post('update_preferred_age_group', [ProfileController::class, 'update_preferred_age_group']);
Route::post('get_states_by_country', [ProfileController::class, 'get_states_by_country']);
Route::post('update_preferred_states', [ProfileController::class, 'update_preferred_states']);
Route::post('public_for_audience_list', [ProfileController::class, 'public_for_audience_list']);
Route::post('get_user_selections', [ProfileController::class, 'get_user_selections']);
Route::post('name_public_select_for_event', [ProfileController::class, 'name_public_select_for_event']);
Route::post('events_search', [ProfileController::class, 'events_search']);
Route::post('my_heritage_events', [ProfileController::class, 'my_heritage_events']);
Route::post('virtual_ahgoo_events', [ProfileController::class, 'virtual_ahgoo_events']);
Route::post('irl_events', [ProfileController::class, 'irl_events']);
Route::post('my_regions', [ProfileController::class, 'my_regions']);
Route::post('event_confirm_attendies', [ProfileController::class, 'event_confirm_attendies']);
Route::post('public_for_audience_list_for_promotion', [ProfileController::class, 'public_for_audience_list_for_promotion']);
Route::post('name_public_select_for_promotion', [ProfileController::class, 'name_public_select_for_promotion']);
Route::post('get_cities_by_country', [ProfileController::class, 'get_cities_by_country']);
Route::post('update_preferred_cities', [ProfileController::class, 'update_preferred_cities']);
Route::post('get_user_by_mobile_or_email', [ProfileController::class, 'get_user_by_mobile_or_email']);
Route::get('test', [ProfileController::class, 'test']);

Route::any('take-git-pull', function () {
    $output = shell_exec('cd /var/www/html/ahgoo/ && sudo git pull');
    return response()->json(['message' => 'Git pull successfull', 'output' => $output], 200);
});