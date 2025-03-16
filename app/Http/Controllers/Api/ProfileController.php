<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AllUser;
use App\Models\Followers;
use App\Models\Friends;
use App\Models\Blocks;
use App\Models\Countries;
use App\Models\Notifications;
use App\Models\InfCatMap;
use App\Models\Posts;
use App\Models\Promotion;
use App\Models\Events;
use App\Models\ProfileViewLog;
use App\Models\KeywordSearchLog;
use App\Models\PostLikes;
use App\Models\Backgrounds;
use App\Models\EventConfirm;
use App\Models\EventMedia;
use App\Models\EventInvites;
use App\Models\Cms;
use App\Models\PreferredSuggestions;
use App\Models\AllLocations;
use App\Models\BookmarkEvent;
use App\Models\Locations;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use WindowsAzure\ServiceManagement\Models\Location;

class ProfileController extends Controller
{
    public function follow(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'followed_to' => 'required|string|max:255',
            'followed_by' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $followed = Followers::create([
                'followed_to' => $request->followed_to,
                'followed_by' => $request->followed_by
            ]);
            $user = AllUser::where('_id', $request->followed_by)->first();
            $notifications = Notifications::create([
                'user_id' => $request->followed_to,
                'relavant_id' => $request->followed_by,
                'relavant_image' => 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg',
                'message' => $user->name.' started following you',
                'type' => 'follow',
                'is_seen' => 0
            ]);
            return response()->json([
                'status' => true,
                'msg' => 'Followed Successfully',
                'data' => (object) []
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Please try again later!',
                'data' => (object) []
            ], 500);
        }
    }
    public function my_profiles(Request $request)
    {
        if(!empty($request->user_id)){
            $user = AllUser::where('_id', $request->user_id)->first();
            $followers_total = Followers::where('followed_to',$request->user_id)->get();
            if(!empty($followers_total)){
                $user->followers = count($followers_total);
            }else{
                $user->followers = 0;
            }
            $user->post = 0; // Replace with your actual method to get followers
            $followed_total = Followers::where('followed_by',$request->user_id)->get();
            if(!empty($followed_total)){
                $user->followed = count($followed_total);
            }else{
                $user->followed = 0;
            }
            $user->friends = 0; // Replace with your actual method to get followers
            $user->videos = 0; // Replace with your actual method to get videos
            $user->amount1 = '0$'; // Replace with your actual method to get followers
            $user->amount2 = '0$'; // Replace with your actual method to get videos
            $user->account_description = 'Love Yourself'; // Replace with your actual method to get videos
            if(!isset($user->profile_pic) OR empty($user->profile_pic)){
                $user->profile_pic = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
            }
            $country =  $user->country;
            $country_details = Countries::where('name', $country)->first();

            $user->country_code = $country_details->phone_code;
            $user->country_flag = $country_details->flag;

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'msg' => "No data found.",
                    'data' => (object) []
                ], 401);
            }
            return response()->json([
                'status' => true,
                'msg' => 'Profile Data.',
                'data' => $user
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'msg' => 'Please provide user id.',
                'data' => (object) []
            ], 422);
        }
    }
    public function unfollow(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unfollowed_to' => 'required|string|max:255',
            'unfollowed_by' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $followed = Followers::where('followed_to', $request->unfollowed_to)
                                ->where('followed_by', $request->unfollowed_by)
                                ->delete();
            return response()->json([
                'status' => true,
                'msg' => 'Unfollowed Successfully',
                'data' => (object) []
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Please try again later!',
                'data' => (object) []
            ], 500);
        }
    }
    public function my_followers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'order' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            if($request->order == 'old'){
                $followers = Followers::where('followed_to', $request->user_id)->orderBy('created_at', 'asc')->get();
            }else if($request->order == 'recent'){
                $followers = Followers::where('followed_to', $request->user_id)->orderBy('created_at', 'desc')->get();
            }else{
                $followers = Followers::where('followed_to',$request->user_id)->get();
            }
            // echo '<pre>';print_r($followers);exit;
            if(!empty($followers)){
                foreach($followers as $follow){
                    $details = AllUser::where('_id', $follow->followed_by)->first();
                    $follow->_id = $details->_id;
                    $follow->name = $details->name;
                    $follow->email = $details->email;
                    $follow->username = $details->username;
                    $follow->phone = $details->phone;
                    $follow->country = $details->country;
                    $follow->user_type = $details->user_type;
                    $followers_total = Followers::where('followed_to',$details->_id)->get();
                    if(!empty($followers_total)){
                        $follow->followers = count($followers_total);
                    }else{
                        $follow->followers = 0;
                    }
                    $follow->post = 0;
                    $followed_total = Followers::where('followed_by',$details->_id)->get();
                    if(!empty($followed_total)){
                        $follow->followed = count($followed_total);
                    }else{
                        $follow->followed = 0;
                    }
                    $follow->friends = 0;
                    $follow->videos = 0;
                    $follow->amount1 = '0$';
                    $follow->amount2 = '0$';
                    $follow->account_description = 'Love Yourself';
                    if(!isset($follow->profile_pic) OR empty($follow->profile_pic)){
                        $follow->profile_pic = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
                    }
                    $country =  $details->country;
                    $country_details = Countries::where('name', $country)->first();
                    if(!empty($country_details)){
                        $follow->country_code = $country_details->phone_code;
                        $follow->country_flag = $country_details->flag;
                    }else{
                        $follow->country_code = '';
                        $follow->country_flag = '';
                    }
                }
                return response()->json([
                    'status' => true,
                    'msg' => 'Follower below',
                    'data' => $followers
                ], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => 'No Followers Found'
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Please try again later!',
                'data' => (object) []
            ], 500);
        }
    }
    public function followings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'order' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            if($request->order == 'old'){
                $followers = Followers::where('followed_by', $request->user_id)->orderBy('created_at', 'asc')->get();
            }else if($request->order == 'recent'){
                $followers = Followers::where('followed_by', $request->user_id)->orderBy('created_at', 'desc')->get();
            }else{
                $followers = Followers::where('followed_by',$request->user_id)->get();
            }
            // echo '<pre>';print_r($followers);exit;
            if(!empty($followers)){
                foreach($followers as $follow){
                    $details = AllUser::where('_id', $follow->followed_by)->first();
                    $follow->_id = $details->_id;
                    $follow->name = $details->name;
                    $follow->email = $details->email;
                    $follow->username = $details->username;
                    $follow->phone = $details->phone;
                    $follow->country = $details->country;
                    $follow->user_type = $details->user_type;
                    $followers_total = Followers::where('followed_to',$details->_id)->get();
                    if(!empty($followers_total)){
                        $follow->followers = count($followers_total);
                    }else{
                        $follow->followers = 0;
                    }
                    $follow->post = 0;
                    $follow->followed = 0;
                    $follow->friends = 0;
                    $follow->videos = 0;
                    $follow->amount1 = '0$';
                    $follow->amount2 = '0$';
                    $follow->account_description = 'Love Yourself';
                    if(!isset($follow->profile_pic) OR empty($follow->profile_pic)){
                        $follow->profile_pic = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
                    }
                    $country =  $details->country;
                    $country_details = Countries::where('name', $country)->first();
                    if(!empty($country_details)){
                        $follow->country_code = $country_details->phone_code;
                        $follow->country_flag = $country_details->flag;
                    }else{
                        $follow->country_code = '';
                        $follow->country_flag = '';
                    }
                }
                return response()->json([
                    'status' => true,
                    'msg' => 'Followings below',
                    'data' => $followers
                ], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => 'No Followings Found'
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Please try again later!',
                'data' => (object) []
            ], 500);
        }
    }
    public function my_freinds(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'order' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            if($request->order == 'all'){
                $all_list = Friends::where(function($query) use ($request) {
                    $query->where('is_accepted', 1)
                          ->where(function($query) use ($request) {
                              $query->where('sent_to', $request->user_id)
                                    ->orWhere('sent_by', $request->user_id);
                          });
                })
                ->orderBy('created_at', 'desc')
                ->get();
            }else if($request->order == 'close'){
                $all_list = Friends::where(function($query) use ($request) {
                    $query->where('is_accepted', 1)
                          ->where(function($query) use ($request) {
                              $query->where('sent_to', $request->user_id)
                                    ->orWhere('sent_by', $request->user_id);
                          });
                })->orderBy('created_at', 'asc')->get();
            }else if($request->order == 'birthday'){
                $all_list = Friends::where(function($query) use ($request) {
                    $query->where('is_accepted', 1)
                          ->where(function($query) use ($request) {
                              $query->where('sent_to', $request->user_id)
                                    ->orWhere('sent_by', $request->user_id);
                          });
                })->orderBy('created_at', 'desc')->get();
            }else{
                $all_list = Friends::where(function($query) use ($request) {
                    $query->where('is_accepted', 1)
                          ->where(function($query) use ($request) {
                              $query->where('sent_to', $request->user_id)
                                    ->orWhere('sent_by', $request->user_id);
                          });
                })->orderBy('created_at', 'desc')->get();
            }
            foreach($all_list as $list){
                if($list->sent_to == $request->user_id){
                    $profile_id = $list->sent_by;
                }else{
                    $profile_id = $list->sent_to;
                }
                $details = AllUser::where('_id', $profile_id)->first();
                $list->time_ago = Carbon::parse($list->created_at)->diffForHumans();
                $list->user_id = $details->_id;
                $list->name = $details->name;
                $list->email = $details->email;
                $list->username = $details->username;
                $list->phone = $details->phone;
                $list->country = $details->country;
                if(!isset($details->profile_pic) OR empty($details->profile_pic)){
                    $list->profile_pic = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
                }else{
                    $list->profile_pic = $details->profile_pic;
                }
            }
            return response()->json([
                'status' => true,
                'data' => $all_list,
                'msg' => 'Frinds Below'
            ], 200);
            // }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Please try again later!',
                'data' => (object) []
            ], 500);
        }
    }
    public function sent_friend_request(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sent_to' => 'required|string|max:255',
            'sent_by' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $followed = Friends::create([
                'sent_to' => $request->sent_to,
                'sent_by' => $request->sent_by,
                'is_accepted' => 0
            ]);
            $user = AllUser::where('_id', $request->sent_by)->first();
            $notifications = Notifications::create([
                'user_id' => $request->sent_to,
                'relavant_id' => $request->sent_by,
                'relavant_image' => $user->profile_pic,
                'message' => $user->name.' sent you friend request',
                'type' => 'friend',
                'is_seen' => 0
            ]);
            return response()->json([
                'status' => true,
                'msg' => 'Friend Request Sent Successfully',
                'data' => (object) []
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Please try again later!',
                'data' => (object) []
            ], 500);
        }
    }
    public function block_user(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'block_to' => 'required|string|max:255',
            'block_by' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $followed = Blocks::create([
                'block_to' => $request->block_to,
                'block_by' => $request->block_by,
                'is_report' => 0
            ]);
            return response()->json([
                'status' => true,
                'msg' => 'User Blocked Successfully',
                'data' => (object) []
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Please try again later!',
                'data' => (object) []
            ], 500);
        }
    }
    public function report_user(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'block_to' => 'required|string|max:255',
            'block_by' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $followed = Blocks::create([
                'block_to' => $request->block_to,
                'block_by' => $request->block_by,
                'is_report' => 1
            ]);
            return response()->json([
                'status' => true,
                'msg' => 'User Reported Successfully',
                'data' => (object) []
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Please try again later!',
                'data' => (object) []
            ], 500);
        }
    }
    public function user_profiles(Request $request)
    {

        if(!empty($request->profile_id)){
            $u_id = $request->user_id;
            $user = AllUser::where('_id', $request->profile_id)->first();
            $followers_total = Followers::where('followed_to',$request->profile_id)->get();
            if(!empty($followers_total)){
                $user->followers = count($followers_total);
            }else{
                $user->followers = 0;
            }
            $user->post = 0; // Replace with your actual method to get followers
            $followed_total = Followers::where('followed_by',$request->profile_id)->get();
            if(!empty($followed_total)){
                $user->followed = count($followed_total);
            }else{
                $user->followed = 0;
            }
            $is_followed = Followers::where('followed_to',$request->profile_id)->where('followed_by',$request->user_id)->first();
            if(!empty($is_followed)){
                $user->is_already_followed = 1;
            }else{
                $user->is_already_followed = 0;
            }
            $user->is_already_freind = 0;
            $is_followed = Friends::where('sent_to',$request->profile_id)->where('sent_by',$request->user_id)->first();
            if(!empty($is_followed)){
                $user->is_friend_req_sent = 1;
            }else{
                $user->is_friend_req_sent = 0;
            }
            $friends = Friends::where('is_accepted', 1)
                ->where(function($query) use ($request) {
                    $query->where('sent_to', $request->profile_id)
                        ->orWhere('sent_by', $request->profile_id);
                })
                ->get();

            $user->friends = count($friends);
            $user->videos = 0; // Replace with your actual method to get videos
            $user->amount1 = '0$'; // Replace with your actual method to get followers
            $user->amount2 = '0$'; // Replace with your actual method to get videos
            $user->account_description = 'Love Yourself'; // Replace with your actual method to get videos
            if(!isset($user->profile_pic) OR empty($user->profile_pic)){
                $user->profile_pic = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
            }

            $country =  $user->country;
            $country_details = Countries::where('name', $country)->first();

            $user->country_code = $country_details->phone_code;
            $user->country_flag = $country_details->flag;

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'msg' => "No data found.",
                    'data' => (object) []
                ], 401);
            }
            ProfileViewLog::where('user_id', $request->user_id)
                       ->where('profile_id', $request->profile_id)
                       ->delete();
            $log_create = ProfileViewLog::create([
                'user_id' => $request->user_id,
                'profile_id' => $request->profile_id,
            ]);
            return response()->json([
                'status' => true,
                'msg' => 'Profile Data.',
                'data' => $user
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'msg' => 'Please provide user id.',
                'data' => (object) []
            ], 422);
        }
    }
    public function notifications(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'last_days' => 'required|string|max:255',
            'type' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $sevenDaysAgo = Carbon::now()->subDays($request->last_days);
            // echo $request->type;exit;
            if($request->type == 'all'){
                // echo 'Hi';exit;
                $notifications = Notifications::where('user_id', $request->user_id)
                                            ->where('created_at', '>=', $sevenDaysAgo)
                                            ->where('is_seen', 1)
                                            ->get();
            }else{
                // echo 'Hello';exit;
                // $notifications = Notifications::where('user_id',$request->user_id)->where('type',$request->type)->get();
                $notifications = Notifications::where('user_id', $request->user_id)
                                            ->where('type',$request->type)
                                            ->where('is_seen', 1)
                                            ->where('created_at', '>=', $sevenDaysAgo)
                                            ->get();
            }
            // echo '<pre>';print_r($notifications);exit;
            if(!$notifications->isEmpty()){
                foreach($notifications as $not){
                    $details = AllUser::where('_id', $not->relavant_id)->first();
                    if(!$details->isEmpty()){
                        $not->relavant_name = $details->name;
                    }else{
                        $not->relavant_name = '';
                    }
                    
                }
            }
            if($request->type == 'all'){
            $new_notifications = Notifications::where('user_id', $request->user_id)
                                                ->where('is_seen', 0)
                                                ->get();
            }else{
                $new_notifications = Notifications::where('user_id', $request->user_id)
                                                ->where('type',$request->type)
                                                ->where('is_seen', 0)
                                                ->get();
            }
            // echo '<pre>';print_r($new_notifications);exit;
            if(!$new_notifications->isEmpty()){
                foreach($new_notifications as $noti){
                    $detailss = AllUser::where('_id', $noti->relavant_id)->first();
                    // echo '<pre>';print_r($details);exit;
                    // echo $detailss->name;exit;
                    if(!empty($detailss)){
                        // echo '1';exit;
                        $noti->relavant_name = $detailss->name;
                    }else{
                        // echo '2';exit;
                        $noti->relavant_name = '';
                    }
                    // echo $noti->relavant_name;exit;
                }
            }
            // echo 'Hello';exit;
            if ($notifications->isEmpty() && $new_notifications->isEmpty()) {
                return response()->json([
                    'status' => true,
                    'msg' => 'No notifications found.',
                    'data' => ['new_notifications' =>[], 'last_7days' =>[],]
                ], 200);
            }
        
            return response()->json([
                'status' => true,
                'msg' => 'Notifications.',
                'data' => ['new_notifications' =>$new_notifications, 'last_7days' =>$notifications,]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Please try again later!',
                'data' => (object) []
            ], 500);
        }
    }
    public function see_notifications(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'notification_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            Notifications::where('user_id', $request->user_id)->where('_id', $request->notification_id)->update(['is_seen' => 1]);
            return response()->json([
                'status' => true,
                'msg' => 'Notification Seen Successfully',
                'data' => (object) [],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Password change failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function profile_step_one(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'step' => 'required|string|max:255',
            'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cover_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $updated_data = array();
            $updated_data['step'] = $request->step;
            $profilePicPath = null;
            if ($request->hasFile('profile_pic')) {
                $profilePicPath = $request->file('profile_pic')->store('profile_pics', 'public');
                $profilePicUrl = Storage::url($profilePicPath);
                $updated_data['profile_pic'] = 'http://34.207.97.193/ahgoo/public'.$profilePicUrl;
            }
            $coverPicPath = null;
            if ($request->hasFile('cover_pic')) {
                $coverPicPath = $request->file('cover_pic')->store('cover_pic', 'public');
                $coverPicUrl = Storage::url($coverPicPath);
                $updated_data['cover_pic'] = 'http://34.207.97.193/ahgoo/public'.$coverPicUrl;
            }
            AllUser::where('_id', $request->user_id)->update($updated_data);

            // $token = $user->createToken('api-token')->plainTextToken;
            $user_data = AllUser::where('_id', $request->user_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Picture Updated Successfully',
                'data' => $user_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function profile_step_two(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'step' => 'required|string|max:255',
            'country1' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            AllUser::where('_id', $request->user_id)->update([
                'country1' => $request->country1,
                'country2' => $request->country2 ?? '',
                'country3' => $request->country3 ?? '',
                'country4' => $request->country4 ?? '',
                'country5' => $request->country5 ?? '',
                'step' => $request->step,
            ]);

            // $token = $user->createToken('api-token')->plainTextToken;
            $user_data = AllUser::where('_id', $request->user_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Profile Updated Successfully',
                'data' => $user_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function profile_step_three(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'step' => 'required|string|max:255',
            'dob' => 'required|date_format:d/m/Y',
            'gender' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $dob = Carbon::createFromFormat('d/m/Y', $request->dob)->format('Y-m-d');
            AllUser::where('_id', $request->user_id)->update([
                'dob' => $dob,
                'gender' => $request->gender,
                'step' => $request->step,
            ]);

            // $token = $user->createToken('api-token')->plainTextToken;
            $user_data = AllUser::where('_id', $request->user_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Profile Updated Successfully',
                'data' => $user_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function profile_step_four(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'step' => 'required|string|max:255',
            'hobby1' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            AllUser::where('_id', $request->user_id)->update([
                'hobby1' => $request->hobby1,
                'hobby2' => $request->hobby2 ?? '',
                'hobby3' => $request->hobby3 ?? '',
                'hobby4' => $request->hobby4 ?? '',
                'hobby5' => $request->hobby5 ?? '',
                'step' => $request->step,
            ]);

            // $token = $user->createToken('api-token')->plainTextToken;
            $user_data = AllUser::where('_id', $request->user_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Profile Updated Successfully',
                'data' => $user_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function influencer_category_map(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'categories' => 'required|array'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }
        try {
            $categories = $request->categories;
            if(!empty($categories)){
                foreach($categories as $cat){
                    $followed = InfCatMap::create([
                        'user_id' => $request->user_id,
                        'category' => $cat
                    ]);
                }
            }
            $cat = InfCatMap::where('user_id', $request->user_id)->get();
            return response()->json([
                'status' => true,
                'msg' => 'Category Added Successfully',
                'data' => $cat
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function influencer_acceptance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }
        try {
            AllUser::where('_id', $request->user_id)->update([
                'user_type' => 2
            ]);
            $user_data = AllUser::where('_id', $request->user_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Influencer Profile Created',
                'data' => $user_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function update_location(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'latitude' => 'required|string|max:255',
            'longitude' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }
        try {
            AllUser::where('_id', $request->user_id)->update([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude
            ]);
            $user_data = AllUser::where('_id', $request->user_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Location Updated Successfully',
                'data' => $user_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function create_post(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'caption' => 'required|string|max:255',
            'media' => 'required|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi,mkv',
            'thumbnail_img' =>'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $updated_data = array();
            $updated_data['user_id'] = $request->user_id;
            $updated_data['caption'] = $request->caption;
            $updated_data['is_active'] = 1;
            $updated_data['is_deleted'] = 0;
            $profilePicPath = null;
            if ($request->hasFile('media')) {
                $mediaPath = $request->file('media')->store('media', 'public');
                $mediaUrl = Storage::url($mediaPath);
                $updated_data['media'] = 'http://34.207.97.193/ahgoo/public'.$mediaUrl;
            }
            if ($request->hasFile('thumbnail_img')) {
                $thumbPicPath = $request->file('thumbnail_img')->store('thumbnail_img', 'public');
                $thumbPicUrl = Storage::url($thumbPicPath);
                $updated_data['thumbnail_img'] = 'http://34.207.97.193/ahgoo/public'.$thumbPicUrl;
            }
            $Posts = Posts::create($updated_data);
            // $user_data = Posts::where('user_id', $request->user_id)->get();
            $insertedId = $Posts->_id;
            $user_data = Posts::where('_id', $insertedId)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Post Added',
                'data' => $user_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function home_page(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            // Step 1: Fetch all posts
            $posts = Posts::orderBy('created_at', 'desc')->get();

            if ($posts->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'No post found',
                    'data' => (object) []
                ], 404);
            }

            // Step 2: Fetch all users based on user_ids from posts in one query
            $userIds = $posts->pluck('user_id')->unique();
            $users = AllUser::whereIn('_id', $userIds)->get()->keyBy('_id');

            // Step 3: Fetch all countries based on country_ids from users in one query
            $countryIds = $users->pluck('country')->unique();
            $countries = Countries::whereIn('name', $countryIds)->get()->keyBy('name');

            // Step 4: Fetch promotion details for all posts in one query
            // echo '<pre>';print_r($posts->pluck('_id'));
            $promotionDetails = Promotion::where('user_id', $request->user_id)
                                ->whereIn('post_id', $posts->pluck('_id'))
                                ->whereIn('is_confirm', ['1', '2', '3'])
                                ->get()
                                ->groupBy('post_id');
            // echo '<pre>';print_r($promotionDetails);exit;

            // Step 5: Fetch all likes for the posts in one query
            $postLikes = PostLikes::whereIn('post_id', $posts->pluck('_id'))
                        ->whereIn('user_id', $userIds)
                        ->get()
                        ->groupBy('post_id');

            // Step 6: Add user, country, promotion, and like data to each post
            $posts = $posts->map(function($post) use ($users, $countries, $promotionDetails, $postLikes) {
                $user = $users->get($post->user_id);
                $country = $user ? $countries->get($user->country) : null;

                $profile_pic = $user && !empty($user->profile_pic) 
                                ? $user->profile_pic 
                                : 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
                
                $is_promotion_added = isset($promotionDetails[$post->_id]) && !$promotionDetails[$post->_id]->isEmpty() 
                                ? $promotionDetails[$post->_id]->first()->is_confirm 
                                : 0;
                if (isset($promotionDetails[$post->_id]) && !$promotionDetails[$post->_id]->isEmpty()) {
                    $promotion = $promotionDetails[$post->_id]->first(); // Get the first record
                
                    $updatedAt = $promotion->updated_at;
                    $totalDays = $promotion->total_days; // Ensure `total_days` is in your Promotion model
                    $totalDays = isset($promotion->total_days) && !empty($promotion->total_days) 
                                ? (int)$promotion->total_days 
                                : 0;
                    $expiryDate = Carbon::parse($updatedAt)->addDays($totalDays);
                    // echo 'EXP '.$post->_id.' '.$expiryDate;exit;
                    if ($expiryDate->greaterThan(Carbon::now())) {
                        $able_to_repromote = 0;
                    }else{
                        $able_to_repromote = 1;
                    }
                }else{
                    $able_to_repromote = 0;
                }
                $promotion_id = isset($promotionDetails[$post->_id]) && !$promotionDetails[$post->_id]->isEmpty() 
                                ? $promotionDetails[$post->_id]->first()->_id 
                                : 0;

                $thumbnail_img = !empty($post->thumbnail_img) 
                                ? $post->thumbnail_img 
                                : 'http://34.207.97.193/ahgoo/storage/profile_pics/video_thum.jpg';

                $is_already_liked = isset($postLikes[$post->_id]) && !$postLikes[$post->_id]->isEmpty() ? 1 : 0;

                return [
                    '_id' => $post->_id,
                    'user_id' => $post->user_id,
                    'caption' => $post->caption,
                    'is_active' => $post->is_active,
                    'is_deleted' => $post->is_deleted,
                    'media' => $post->media,
                    'updated_at' => $post->updated_at,
                    'created_at' => $post->created_at,
                    'user_name' => $user ? $user->name : '',
                    'profile_pic' => $profile_pic,
                    'country' => $user ? $user->country : '',
                    'flag' => $country ? $country->flag : '',
                    'mi_flag' => $country ? $country->mi_flag : '',
                    'is_promotion_created' => $is_promotion_added,
                    'promotion_id' => $promotion_id,
                    'thumbnail_img' => $thumbnail_img,
                    'is_already_liked' => $is_already_liked,
                    'able_to_repromote' => $able_to_repromote
                ];
            });

            return response()->json([
                'status' => true,
                'msg' => 'Post data fetched',
                'data' => $posts
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'No Post Found',
                'data' => (object) []
            ], 500);
        }
    }
    public function create_promotion_1(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'post_id' => 'required|string|max:255',
            'cover_pic' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_showing_event' => 'required|integer|in:0,1',
            'type' => 'required|string|max:255',
            'plan_active_days' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            // if(isset($request->event_type) && !empty($request->event_type)){
            //     $e_type = $request->event_type;
            // }else{
            //     $e_type = 2;
            // }
            // Store the cover image
            $coverImage = $request->file('cover_pic');
            $path = $coverImage->store('event_media', 'public');
            $thumbPicUrl = Storage::url($path);
            $pth = 'http://34.207.97.193/ahgoo/public'.$thumbPicUrl;

            $promotion = Promotion::create([
                'user_id' => $request->user_id,
                'post_id' => $request->post_id,
                'is_showing_event' => $request->is_showing_event,
                'type' => $request->type,
                'web_address' => $request->web_address ?? '',
                'cover_pic' => $pth,
                'plan_active_days' => $request->plan_active_days ?? 0,
            ]);
            $insertedId = $promotion->_id;
            // $token = $user->createToken('api-token')->plainTextToken;

            if($request->is_showing_event == 1){
                $eventDate = $request->event_date ?? '';
                $eventEndDate = $request->event_end_date ?? '';

                if (!empty($eventDate)) {
                    $eventDate = Carbon::createFromFormat('Y-m-d', $request->event_date)->format('Y-m-d');
                }

                if (!empty($eventEndDate)) {
                    $eventEndDate = Carbon::createFromFormat('Y-m-d', $request->event_end_date)->format('Y-m-d');
                }

                $event = Events::create([
                    'event_name' => $request->event_name,
                    'is_showing_event' => $request->is_showing_event,
                    'event_date' => $eventDate,
                    'event_end_date' => $eventEndDate,
                    'web_address' => $request->web_address ?? '',
                    'is_confirm' => 1
                ]);

                $event_id = $event->_id;

                EventMedia::create([
                    'event_id' => $event_id,
                    'media_path' => $pth,
                    'media_type' => 'image',
                ]);
            }

            $nodeRequestData = [
                'postId' => $request->post_id,
                'coverPic' => $pth,
                'isShowingEvent' => $request->is_showing_event,
                'webAddress' => $request->web_address,
                'userId' => $request->user_id,
                'type' => $request->type,
            ];

            if(!empty($request->plan_active_days)){
                $nodeRequestData['planActiveDays'] = $request->plan_active_days;
            }

            Http::post('https://dev-api.ahgoo.com/v1/post/adsDetail', $nodeRequestData);
            
            $promo_data = Promotion::where('_id', $insertedId)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Promotion added successfully',
                'data' => $promo_data
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) [$e->getMessage()]
            ], 500);
        }
    }
    public function create_promotion_2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'promotion_id' => 'required|string|max:255',
            // 'automatic_public' => 'required|integer|in:0,1',
            // 'is_name_public_already_created' => 'required|integer|in:0,1'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            Promotion::where('_id', $request->promotion_id)->update([
                'automatic_public' => $request->automatic_public ?? 0,
                'is_name_public_already_created' => $request->is_name_public_already_created ?? 0
            ]);
            $promo_data = Promotion::where('_id', $request->promotion_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Promotion updated successfully',
                'data' => $promo_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function create_promotion_audience(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'promotion_id' => 'required|string|max:255',
            'estimated_size' => 'required|string|max:255',
            'name_of_audience' => 'required|string|max:255',
            'age_from' => 'required|integer',
            'age_to' => 'required|integer',
            'gender' => 'required|string|max:255',
            'location' => 'required|array'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $locationJson = json_encode($request->location);
            Promotion::where('_id', $request->promotion_id)->update([
                'estimated_size' => $request->estimated_size,
                'name_of_audience' => $request->name_of_audience,
                'age_from' => $request->age_from,
                'age_to' => $request->age_to,
                'gender' => $request->gender,
                'location' => $locationJson
            ]);
            $promo_data = Promotion::where('_id', $request->promotion_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Promotion updated successfully',
                'data' => $promo_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function create_promotion_budget(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'promotion_id' => 'required|string|max:255',
            'per_day_spent' => 'required|integer',
            'total_days' => 'required|integer'
            // 'event_location' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $locationJson = json_encode($request->location);
            Promotion::where('_id', $request->promotion_id)->update([
                'per_day_spent' => $request->per_day_spent,
                'total_days' => $request->total_days,
                'total_cost' => $request->per_day_spent*$request->total_days
                // 'event_location' => $request->event_location
            ]);
            $promo_data = Promotion::where('_id', $request->promotion_id)->first();
            $promo_data->total_cost = number_format($promo_data->total_cost);
            return response()->json([
                'status' => true,
                'msg' => 'Promotion updated successfully',
                'data' => $promo_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function create_promotion_payment_method(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'promotion_id' => 'required|string|max:255',
            'payment_method' => 'required|integer'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            Promotion::where('_id', $request->promotion_id)->update([
                'payment_method' => $request->payment_method
            ]);
            $promo_data = Promotion::where('_id', $request->promotion_id)->first();
            $slug = 'promotion_confirmation';
            $cms_data = Cms::where('slug', 'LIKE', "%{$slug}%")
                        ->first();
            return response()->json([
                'status' => true,
                'msg' => 'Promotion updated successfully',
                'data' => $promo_data,
                'popup_cms_title' => $cms_data->title,
                'popup_cms_content' => $cms_data->content
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function create_promotion_confirm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'promotion_id' => 'required|string|max:255',
            'is_confirm' => 'required|integer'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $locationJson = json_encode($request->location);
            Promotion::where('_id', $request->promotion_id)->update([
                'is_confirm' => $request->is_confirm
            ]);
            $promo_data = Promotion::where('_id', $request->promotion_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Promotion updated successfully',
                'data' => $promo_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function audience_name_check(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_of_audience' => 'required|string|min:5',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json([
                'status' => false,
                'data' => (object) [],
                'msg' => $errors[0]
            ], 422);
        }
        try {
            $name_of_audience = $request->name_of_audience;

            // Search for users where name, email, or username contains the keyword
            $users = Promotion::where('name_of_audience', 'LIKE', "%{$name_of_audience}%")
                        ->get();

            // Check if users were found
            if ($users->isEmpty()) {
                return response()->json([
                    'status' => true,
                    'msg' => 'Audience name is available',
                    'data' => (object) []
                ], 200);
            }

            return response()->json([
                'status' => false,
                'msg' => 'Audience name not available.',
                'data' => (object) []
            ], 404);
        }catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Failed!',
                'data' => (object) []
            ], 500);
        }
    }
    public function delete_promotion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'promotion_id' => 'required|string|max:255',
            'user_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            Promotion::where('_id', $request->promotion_id)
                       ->where('user_id', $request->user_id)
                       ->delete();
            return response()->json([
                'status' => true,
                'msg' => 'Promotion deleted successfully',
                'data' => (object) []
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function create_promotion_target(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'promotion_id' => 'required|string|max:255',
            'target_scope' => 'required|integer',
            'target_interaction' => 'required|integer',
            'target_profile_visits' => 'required|integer'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $locationJson = json_encode($request->location);
            Promotion::where('_id', $request->promotion_id)->update([
                'target_scope' => $request->target_scope,
                'target_interaction' => $request->target_interaction,
                'target_profile_visits' => $request->target_profile_visits
            ]);
            $promo_data = Promotion::where('_id', $request->promotion_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Promotion updated successfully',
                'data' => $promo_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function promotion_details(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'promotion_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $promo_data = Promotion::where('_id', $request->promotion_id)->first();
            $promo_data->total_cost = number_format($promo_data->total_cost);
            $post_id = $promo_data->post_id;
            $promo_data->total_plays = rand(101, 200);
            $promo_data->total_likes = PostLikes::where('post_id', $post_id)->count();
            $promo_data->total_comments = rand(10, 20);
            $promo_data->total_shares = rand(2, 9);
            $promo_data->total_bookmarks = rand(1, 8);
            $promo_data->scopes = rand(1, 9);
            $promo_data->total_interactions = rand(10, 200);
            $promo_data->total_profile_views = rand(10, 20);
            $promo_data->reproductions_left_text = "(+11hr from yesterday)";
            $promo_data->reproductions_right_text = "1450 Hr / 16 min / 24 seg";
            $promo_data->reproductions_is_positive = 1;
            $promo_data->avg_viewing_time_left_text = "(-0,5 from yesterday)";
            $promo_data->avg_viewing_time_right_text = "1450 Hr / 16 min / 24 seg";
            $promo_data->avg_viewing_time_is_positive = 0;
            $promo_data->audience_reached = rand(100, 999);
            $promo_data->audience_reached_text = "(+700 from yesterday)";
            $promo_data->audience_reached_is_positive = 1;

            $countries = [
                ['name' => 'USA', 'percentage' => 35],
                ['name' => 'India', 'percentage' => 30],
                ['name' => 'Argentina', 'percentage' => 20],
                ['name' => 'Spain', 'percentage' => 15],
            ];
            
            $country_counts = [];
            
            foreach ($countries as $country) {
                $count = round(($country['percentage'] / 100) * $promo_data->audience_reached);
                $country_counts[] = [
                    'name' => $country['name'],
                    'percentage' => $country['percentage'],
                    'count' => $count,
                ];
            }
            $promo_data->audience_reached_country_wise = $country_counts;
            
            return response()->json([
                'status' => true,
                'msg' => 'Promotion details below',
                'data' => $promo_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function uploadEventImages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json([
                'status' => false,
                'data' => (object) [],
                'msg' => $errors[0]
            ], 422);
        }

        try {
            // Check if event_id is passed and not empty, if not, create a new Event
            if ($request->has('event_id') && !empty($request->event_id)) {
                $event_id = $request->event_id;
            } else {
                $event = Events::create([
                    // Add any necessary default values for the new Event here
                ]);
                $event_id = $event->_id;
            }

            $uploadedImages = [];

            foreach ($request->file('images') as $image) {
                $path = $image->store('event_media', 'public');
                $thumbPicUrl = Storage::url($path);
                $pth = 'http://34.207.97.193/ahgoo/public' . $thumbPicUrl;
                $uploadedImages[] = EventMedia::create([
                    'event_id' => $event_id,
                    'media_path' => $pth,
                    'media_type' => 'image',
                ]);
            }

            return response()->json([
                'status' => true,
                'msg' => 'Images uploaded successfully',
                'data' => $uploadedImages
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }

    public function uploadEventVideo(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimetypes:video/avi,video/mpeg,video/mp4|max:20480', // Max 20MB
        ]);

        // Check if event_id is passed and not empty, if not, create a new Event
        if ($request->has('event_id') && !empty($request->event_id)) {
            $event_id = $request->event_id;
        } else {
            $event = Events::create([
                // Add any necessary default values for the new Event here
            ]);
            $event_id = $event->_id;
        }

        $video = $request->file('video');
        $path = $video->store('event_media', 'public');

        // Check video duration (uncomment and adjust this section if FFMpeg is installed)
        /*
        $ffmpeg = FFMpeg::create();
        $videoFile = $ffmpeg->open(Storage::disk('public')->path($path));
        $duration = $videoFile->getFormat()->get('duration');

        if ($duration > 15) {
            // Delete the uploaded file if it exceeds the duration limit
            Storage::disk('public')->delete($path);

            return response()->json([
                'status' => false,
                'msg' => 'Video duration exceeds 15 seconds',
                'data' => (object)[]
            ], 400);
        }
        */

        $thumbPicUrl = Storage::url($path);
        $pth = 'http://34.207.97.193/ahgoo/public' . $thumbPicUrl;
        $uploadedVideo = EventMedia::create([
            'event_id' => $event_id,
            'media_path' => $pth,
            'media_type' => 'video',
            'video_duration' => '', // Replace with $duration if FFMpeg is used
        ]);

        return response()->json([
            'status' => true,
            'msg' => 'Video uploaded successfully',
            'data' => $uploadedVideo
        ], 201);
    }
    public function my_posts(Request $request)
    {
        if(!empty($request->user_id)){
            $posts = Posts::where('user_id', $request->user_id)->get();
            if ($posts->isEmpty()) {
                return response()->json([
                    'status' => true,
                    'msg' => "No posts found.",
                    'data' => (object) []
                ], 401);
            }
            foreach($posts as $post){
                if(!isset($post->thumbnail_img) OR empty($post->thumbnail_img)){
                    $post->thumbnail_img = 'http://34.207.97.193/ahgoo/storage/profile_pics/video_thum.jpg';
                }
            }
            return response()->json([
                'status' => true,
                'msg' => 'Posts below',
                'data' => $posts
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'msg' => 'Please provide user id.',
                'data' => (object) []
            ], 422);
        }
    }
    public function create_post_thumbnail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'post_id' => 'required|string|max:255',
            'thumbnail_img' =>'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $updated_data = array();
            $profilePicPath = null;
            if ($request->hasFile('thumbnail_img')) {
                $thumbPicPath = $request->file('thumbnail_img')->store('thumbnail_img', 'public');
                $thumbPicUrl = Storage::url($thumbPicPath);
                $updated_data['thumbnail_img'] = 'http://34.207.97.193/ahgoo/public'.$thumbPicUrl;
            }
            $Posts = Posts::where('user_id', $request->user_id)->where('_id', $request->post_id)->update($updated_data);
            $user_data = Posts::where('_id', $request->post_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Thumbnail Image Added',
                'data' => $user_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function home_search_for_you(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'keyword' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {

            // Step 1: Fetch all posts
            if(!empty($request->keyword)){
                KeywordSearchLog::create([
                    'user_id' => $request->user_id,
                    'keyword' => $request->keyword
                ]);
                $posts = Posts::where('caption', 'LIKE', "%{$request->keyword}%")->orderBy('created_at', 'desc')->get();
            }else{
                $posts = Posts::orderBy('created_at', 'desc')->get();
            }
            

            if ($posts->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'No post found',
                    'data' => (object) []
                ], 404);
            }

            // Step 2: Fetch all users based on user_ids from posts
            $userIds = $posts->pluck('user_id')->unique();
            $users = AllUser::whereIn('_id', $userIds)->get()->keyBy('_id');

            // Step 3: Fetch all countries based on country_ids from users
            $countryIds = $users->pluck('country')->unique();
            $countries = Countries::whereIn('name', $countryIds)->get()->keyBy('name');

            // Step 4: Add user and country data to each post
            $posts = $posts->map(function($post) use ($users, $countries) {
                $user = $users->get($post->user_id);
                $country = $user ? $countries->get($user->country) : null;
                if(!isset($user->profile_pic) OR empty($user->profile_pic)){
                    $profile_pic = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
                }else{
                    $profile_pic = $user->profile_pic;
                }
                $promotion_det = Promotion::where("post_id",$post->_id)->get();
                $is_promotion_added = $promotion_det->isEmpty() ? 0 : 1;   
                if(!isset($post->thumbnail_img) OR empty($post->thumbnail_img)){
                    $thumbnail_img = 'http://34.207.97.193/ahgoo/storage/profile_pics/video_thum.jpg';
                }else{
                    $thumbnail_img = $post->thumbnail_img;
                }     
                return [
                    '_id' => $post->_id,
                    'user_id' => $post->user_id,
                    'caption' => $post->caption,
                    'is_active' => $post->is_active,
                    'is_deleted' => $post->is_deleted,
                    'media' => $post->media,
                    'updated_at' => $post->updated_at,
                    'created_at' => $post->created_at,
                    'user_name' => $user ? $user->name : '',
                    'profile_pic' => $profile_pic,
                    'country' => $user ? $user->country : '',
                    'flag' => $country ? $country->flag : '',
                    'mi_flag' => $country ? $country->mi_flag : '',
                    'is_promotion_created' => $is_promotion_added,
                    'thumbnail_img' => $thumbnail_img
                ];
            });
            return response()->json([
                'status' => true,
                'msg' => 'Post data fetched',
                'data' => $posts
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'No Post Found',
                'data' => (object) []
            ], 500);
        }
    }
    public function home_search_accounts(Request $request)
    {
        if (empty($request->user_id)) {
            return response()->json([
                'status' => false,
                'msg' => 'Please provide user id.',
                'data' => (object) []
            ], 422);
        }

        // Fetch all users except the one with the specified user_id
        if(!empty($request->keyword)){
            KeywordSearchLog::create([
                'user_id' => $request->user_id,
                'keyword' => $request->keyword
            ]);
            $users = AllUser::where('_id', '!=', $request->user_id)
                    ->where('name', 'LIKE', "%{$request->keyword}%")
                    ->orWhere('email', 'LIKE', "%{$request->keyword}%")
                    ->orWhere('username', 'LIKE', "%{$request->keyword}%")
                    ->get();
        }else{
            $users = AllUser::where('_id', '!=', $request->user_id)->get();
        }
        

        if ($users->isEmpty()) {
            return response()->json([
                'status' => false,
                'msg' => "No Account Found.",
                'data' => (object) []
            ], 401);
        }

        // Fetch all follower relationships where the followed_to is in the user list
        $followerIds = $users->pluck('_id')->toArray();
        $followers = Followers::whereIn('followed_to', $followerIds)->get();

        // Pre-compute user follow statuses
        $followedStatuses = $followers->where('followed_by', $request->user_id)->pluck('followed_to')->toArray();
        $followerCounts = $followers->groupBy('followed_to')->map->count();

        foreach ($users as $user) {
            $user->is_already_followed = in_array($user->_id, $followedStatuses) ? 1 : 0;
            $user->is_already_freind = 0; // Assuming you will update this as needed
            $user->followers = $followerCounts[$user->_id] ?? 0;
            $user->videos = 0; // Replace with actual method to get videos
            $user->freinds = 0; // Replace with actual method to get friends
            $user->account_description = 'Love Yourself'; // Replace with your actual method to get account description
            if(!isset($user->profile_pic) OR empty($user->profile_pic)){
            $user->profile_pic = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
            }
        }
        return response()->json([
            'status' => true,
            'msg' => 'All Accounts.',
            'data' => $users
        ], 200);
    }
    public function home_search_videos(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'keyword' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            // Step 1: Fetch all posts
            if(!empty($request->keyword)){
                KeywordSearchLog::create([
                    'user_id' => $request->user_id,
                    'keyword' => $request->keyword
                ]);
                $posts = Posts::where('caption', 'LIKE', "%{$request->keyword}%")->orderBy('created_at', 'desc')->get();
            }else{
                $posts = Posts::orderBy('created_at', 'desc')->get();
            }
            

            if ($posts->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'No post found',
                    'data' => (object) []
                ], 404);
            }

            // Step 2: Fetch all users based on user_ids from posts
            $userIds = $posts->pluck('user_id')->unique();
            $users = AllUser::whereIn('_id', $userIds)->get()->keyBy('_id');

            // Step 3: Fetch all countries based on country_ids from users
            $countryIds = $users->pluck('country')->unique();
            $countries = Countries::whereIn('name', $countryIds)->get()->keyBy('name');

            // Step 4: Add user and country data to each post
            $posts = $posts->map(function($post) use ($users, $countries) {
                $user = $users->get($post->user_id);
                $country = $user ? $countries->get($user->country) : null;
                if(!isset($user->profile_pic) OR empty($user->profile_pic)){
                    $profile_pic = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
                }else{
                    $profile_pic = $user->profile_pic;
                }
                $promotion_det = Promotion::where("post_id",$post->_id)->get();
                $is_promotion_added = $promotion_det->isEmpty() ? 0 : 1;   
                if(!isset($post->thumbnail_img) OR empty($post->thumbnail_img)){
                    $thumbnail_img = 'http://34.207.97.193/ahgoo/storage/profile_pics/video_thum.jpg';
                }else{
                    $thumbnail_img = $post->thumbnail_img;
                }     
                return [
                    '_id' => $post->_id,
                    'user_id' => $post->user_id,
                    'caption' => $post->caption,
                    'is_active' => $post->is_active,
                    'is_deleted' => $post->is_deleted,
                    'media' => $post->media,
                    'updated_at' => $post->updated_at,
                    'created_at' => $post->created_at,
                    'user_name' => $user ? $user->name : '',
                    'profile_pic' => $profile_pic,
                    'country' => $user ? $user->country : '',
                    'flag' => $country ? $country->flag : '',
                    'mi_flag' => $country ? $country->mi_flag : '',
                    'is_promotion_created' => $is_promotion_added,
                    'thumbnail_img' => $thumbnail_img
                ];
            });
            return response()->json([
                'status' => true,
                'msg' => 'Post data fetched',
                'data' => $posts
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'No Post Found',
                'data' => (object) []
            ], 500);
        }
    }
    public function latest_profile_views(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $all_profs = ProfileViewLog::where('user_id',$request->user_id)->orderBy('created_at', 'desc')->limit(5)->get();
            if (!$all_profs) {
                return response()->json([
                    'status' => true,
                    'msg' => "No data found.",
                    'data' => (object) []
                ], 401);
            }
            foreach ($all_profs as $user) {
                $u_id = $request->user_id;
                
                // Fetch the AllUser object but don't reassign it to $user
                $allUser = AllUser::where('_id', $user->profile_id)->first();
                
                // Update the current $user object with properties from the fetched AllUser object
                if ($allUser) {
                    $user->user_name = $allUser->name;
                    $user->profile_pic = $allUser->profile_pic ?? 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
                    $user->country = $allUser->country;
                }
                
                // Get followers count
                $followers_total = Followers::where('followed_to', $user->profile_id)->count();
                $user->followers = $followers_total;
            
                // Static or placeholder values
                $user->post = 0;
                
                // Get followed count
                $followed_total = Followers::where('followed_by', $user->profile_id)->count();
                $user->followed = $followed_total;
            
                // Check if the user is already followed
                $is_followed = Followers::where('followed_to', $user->profile_id)->where('followed_by', $request->user_id)->exists();
                $user->is_already_followed = $is_followed ? 1 : 0;
            
                // Friend request status
                $is_friend_req_sent = Friends::where('sent_to', $user->profile_id)->where('sent_by', $request->user_id)->exists();
                $user->is_friend_req_sent = $is_friend_req_sent ? 1 : 0;
            
                // Additional static or placeholder values
                $user->friends = 0;
                $user->videos = 0;
                $user->amount1 = '0$';
                $user->amount2 = '0$';
                $user->account_description = 'Love Yourself';
            
                // Set country-related data
                $country_details = Countries::where('name', $user->country)->first();
                if ($country_details) {
                    $user->country_code = $country_details->phone_code;
                    $user->country_flag = $country_details->flag;
                } else {
                    $user->country_code = null;
                    $user->country_flag = null;
                }
            }
            return response()->json([
                'status' => true,
                'msg' => 'Latest Profile Views',
                'data' => $all_profs
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'No Details Found',
                'data' => (object) []
            ], 500);
        }
    }
    public function latest_keyword_search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $keyword = KeywordSearchLog::where('user_id',$request->user_id)->orderBy('created_at', 'desc')->limit(10)->get();
            return response()->json([
                'status' => true,
                'msg' => 'Latest Profile Views',
                'data' => $keyword
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'No Details Found',
                'data' => (object) []
            ], 500);
        }
    }
    public function like_post(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'post_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $delete = PostLikes::where('user_id', $request->user_id)
                                ->where('post_id', $request->post_id)
                                ->delete();
            $like = PostLikes::create([
                'user_id' => $request->user_id,
                'post_id' => $request->post_id
            ]);
            return response()->json([
                'status' => true,
                'msg' => 'Post Liked Successfully',
                'data' => (object) []
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Some error occured',
                'data' => (object) []
            ], 500);
        }
    }
    public function dislike_post(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'post_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $delete = PostLikes::where('user_id', $request->user_id)
                                ->where('post_id', $request->post_id)
                                ->delete();
            return response()->json([
                'status' => true,
                'msg' => 'Post Disliked Successfully',
                'data' => (object) []
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Some error occured',
                'data' => (object) []
            ], 500);
        }
    }
    public function event_background_videos(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $videos = Backgrounds::all();

            if ($videos->isNotEmpty()) {
                $randomVideo = $videos->random(); // Laravel's Collection random method
                // You now have a random video from the collection
            }
            return response()->json([
                'status' => true,
                'msg' => 'Background videos follows',
                'data' => $randomVideo
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Some error occured',
                'data' => (object) []
            ], 500);
        }
    }
    public function recent_events(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            // if(!empty($request->category)){
            //     $promotions = Events::where('is_confirm','1')->where('event_category',$request->category)->orderBy('created_at', 'desc')->limit(10)->get();
            // }else{
            //     $promotions = Events::where('is_confirm','1')->orderBy('created_at', 'desc')->limit(10)->get();
            // }

            $where = [];
            $orderBy = [];
            $where[] = ['is_confirm', '=', '1'];
            if ($request->has('category') && !empty($request->category)) {
                $where[] = ['event_category', '=', $request->category];
            }
            if ($request->has('keyword') && !empty($request->keyword)) {
                $where[] = ['event_name', 'LIKE', "%{$request->keyword}%"];
            }
            if ($request->has('region') && !empty($request->region)) {
                $all_locations = Locations::where('country', $request->region)->pluck('name')->toArray();
                $where[] = ['location', '=', $all_locations];
            }
            if ($request->has('sort_by')) {
                if($request->sort_by == 'date_asc'){
                    $orderBy[] = ['created_at', 'asc'];
                }else if($request->sort_by == 'date_desc'){
                    $orderBy[] = ['created_at', 'desc'];
                }else if($request->sort_by == 'name_asc'){
                    $orderBy[] = ['event_name', 'asc'];
                }else if($request->sort_by == 'name_desc'){
                    $orderBy[] = ['event_name', 'desc'];
                }else{
                    $orderBy[] = ['created_at', 'desc'];
                }
                $orderBy[] = [$request->order_by_field, $request->order_by_direction];
            } else {
                $orderBy[] = ['created_at', 'desc'];
            }
            // echo '<pre>';print_r($orderBy);exit;
            $query = Events::query();
            foreach ($where as $condition) {
                if (is_array($condition[2])) {
                    // If the condition value is an array, use whereIn
                    $query->whereIn($condition[0], $condition[2]);
                } else {
                    $query->where($condition[0], $condition[1], $condition[2]);
                }
            }
            foreach ($orderBy as $order) {
                if(!empty($order[0])){
                    $query->orderBy($order[0], $order[1]);
                }
            }
            $query->limit(10);
            $promotions = $query->get();
            // echo '<pre>';print_r($promotions);exit;
            foreach($promotions as $promo){
                $promo->formatted_event_date = 'Invalid date';
                // $promo->images = 'http://34.207.97.193/ahgoo/storage/profile_pics/event_iamge.jpeg';
                try {
                    $promo->formatted_event_date = Carbon::createFromFormat('Y-m-d', $promo->event_date)->format('d M');
                } catch (\Exception $e) {
                    try {
                        $promo->formatted_event_date = Carbon::parse($promo->event_date)->format('d M');
                    } catch (\Exception $e) {
                        $promo->formatted_event_date = 'Invalid date';
                    }
                }
                $promo->users = (object) ['http://34.207.97.193/ahgoo/public/storage/profile_pics/9n4Iib5TeWy4rg7r8ThmHUm68yyXAnKEyeIJRrme.jpg','http://34.207.97.193/ahgoo/public/storage/profile_pics/zvHXOR1FvMfEDAhI7keSGWSSEHQoAR2DqpduS3OL.jpg','http://34.207.97.193/ahgoo/public/storage/profile_pics/aUWcn7KmzHDEckC67yPRCidOrItNY96Hsz19YN8w.jpg'];

                $eventJoinedUserIDs = EventConfirm::where('event_id', $promo->id)->pluck('user_id');
                if (!empty($eventJoinedUserIDs)) {
                    $joinedUsers = AllUser::whereIn('_id', $eventJoinedUserIDs)->get()->map(function ($user) {
                        return [
                            'user_id' => $user->_id,
                            'name' => $user->name,
                            'profile_pic' => $user->profile_pic ?? null
                        ];
                    });
                    $promo->event_joined_users = $joinedUsers;
                } else {
                    $promo->event_joined_users = [];
                }
            }
            return response()->json([
                'status' => true,
                'msg' => 'Recent Events Below',
                'data' => $promotions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Some error occured',
                'data' => (object) []
            ], 500);
        }
    }
    public function upload_background_videos(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'media' => 'required|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi,mkv'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $updated_data = array();
            $profilePicPath = null;
            if ($request->hasFile('media')) {
                $profilePicPath = $request->file('media')->store('background_videos', 'public');
                $profilePicUrl = Storage::url($profilePicPath);
                $updated_data['media'] = 'http://34.207.97.193/ahgoo/public'.$profilePicUrl;
            }
            Backgrounds::create($updated_data);
            return response()->json([
                'status' => true,
                'msg' => 'Video Uploaded',
                'data' => (object) []
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function event_suggesstion_posts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            // Step 1: Fetch all posts
            $posts = Posts::orderBy('created_at', 'desc')->limit(4)->get();

            if ($posts->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'No post found',
                    'data' => (object) []
                ], 404);
            }

            // Step 2: Fetch all users based on user_ids from posts in one query
            $userIds = $posts->pluck('user_id')->unique();
            $users = AllUser::whereIn('_id', $userIds)->get()->keyBy('_id');

            // Step 3: Fetch all countries based on country_ids from users in one query
            $countryIds = $users->pluck('country')->unique();
            $countries = Countries::whereIn('name', $countryIds)->get()->keyBy('name');

            // Step 4: Fetch promotion details for all posts in one query
            $promotionDetails = Promotion::whereIn('post_id', $posts->pluck('_id'))->get()->groupBy('post_id');

            // Step 5: Fetch all likes for the posts in one query
            $postLikes = PostLikes::whereIn('post_id', $posts->pluck('_id'))
                        ->whereIn('user_id', $userIds)
                        ->get()
                        ->groupBy('post_id');

            // Step 6: Add user, country, promotion, and like data to each post
            $posts = $posts->map(function($post) use ($users, $countries, $promotionDetails, $postLikes) {
                $user = $users->get($post->user_id);
                $country = $user ? $countries->get($user->country) : null;

                $profile_pic = $user && !empty($user->profile_pic) 
                                ? $user->profile_pic 
                                : 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
                
                $is_promotion_added = isset($promotionDetails[$post->_id]) && !$promotionDetails[$post->_id]->isEmpty() ? 1 : 0;

                $thumbnail_img = !empty($post->thumbnail_img) 
                                ? $post->thumbnail_img 
                                : 'http://34.207.97.193/ahgoo/storage/profile_pics/video_thum.jpg';

                $is_already_liked = isset($postLikes[$post->_id]) && !$postLikes[$post->_id]->isEmpty() ? 1 : 0;

                return [
                    '_id' => $post->_id,
                    'user_id' => $post->user_id,
                    'caption' => $post->caption,
                    'is_active' => $post->is_active,
                    'is_deleted' => $post->is_deleted,
                    'media' => $post->media,
                    'updated_at' => $post->updated_at,
                    'created_at' => $post->created_at,
                    'user_name' => $user ? $user->name : '',
                    'profile_pic' => $profile_pic,
                    'country' => $user ? $user->country : '',
                    'flag' => $country ? $country->flag : '',
                    'mi_flag' => $country ? $country->mi_flag : '',
                    'is_promotion_created' => $is_promotion_added,
                    'thumbnail_img' => $thumbnail_img,
                    'is_already_liked' => $is_already_liked
                ];
            });

            return response()->json([
                'status' => true,
                'msg' => 'Post data fetched',
                'data' => $posts
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'No Post Found',
                'data' => (object) []
            ], 500);
        }
    }
    public function recent_events_your_countries(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            if(!empty($request->category)){
                $promotions = Events::where('is_confirm','1')->where('event_category',$request->category)->limit(10)->get();
            }else{
                $promotions = Events::where('is_confirm','1')->limit(10)->get();
            }
            foreach($promotions as $promo){
                // $promo->images = 'http://34.207.97.193/ahgoo/storage/profile_pics/event_iamge.jpeg';
                try {
                    $promo->formatted_event_date = Carbon::createFromFormat('Y-m-d', $promo->event_date)->format('d M');
                } catch (\Exception $e) {
                    try {
                        $promo->formatted_event_date = Carbon::parse($promo->event_date)->format('d M');
                    } catch (\Exception $e) {
                        $promo->formatted_event_date = 'Invalid date';
                    }
                }
                $promo->users = (object) ['http://34.207.97.193/ahgoo/public/storage/profile_pics/9n4Iib5TeWy4rg7r8ThmHUm68yyXAnKEyeIJRrme.jpg','http://34.207.97.193/ahgoo/public/storage/profile_pics/zvHXOR1FvMfEDAhI7keSGWSSEHQoAR2DqpduS3OL.jpg','http://34.207.97.193/ahgoo/public/storage/profile_pics/aUWcn7KmzHDEckC67yPRCidOrItNY96Hsz19YN8w.jpg'];
            }
            return response()->json([
                'status' => true,
                'msg' => 'Recent Events Below',
                'data' => $promotions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Some error occured',
                'data' => (object) []
            ], 500);
        }
    }
    public function recent_events_near_you(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $where = [];
            $orderBy = [];
            $where[] = ['is_confirm', '=', '1'];
            if ($request->has('category') && !empty($request->category)) {
                $where[] = ['event_category', '=', $request->category];
            }
            if ($request->has('region') && !empty($request->region)) {
                $all_locations = Locations::where('country', $request->region)->pluck('name')->toArray();
                $where[] = ['location', '=', $all_locations];
            }
            if ($request->has('sort_by')) {
                if($request->sort_by == 'date_asc'){
                    $orderBy[] = ['created_at', 'asc'];
                }else if($request->sort_by == 'date_desc'){
                    $orderBy[] = ['created_at', 'desc'];
                }else if($request->sort_by == 'name_asc'){
                    $orderBy[] = ['event_name', 'asc'];
                }else if($request->sort_by == 'name_desc'){
                    $orderBy[] = ['event_name', 'desc'];
                }else{
                    $orderBy[] = ['created_at', 'desc'];
                }
                $orderBy[] = [$request->order_by_field, $request->order_by_direction];
            } else {
                $orderBy[] = ['created_at', 'desc'];
            }
            // echo '<pre>';print_r($orderBy);exit;
            $query = Events::query();
            foreach ($where as $condition) {
                if (is_array($condition[2])) {
                    // If the condition value is an array, use whereIn
                    $query->whereIn($condition[0], $condition[2]);
                } else {
                    $query->where($condition[0], $condition[1], $condition[2]);
                }
            }
            foreach ($orderBy as $order) {
                if(!empty($order[0])){
                    $query->orderBy($order[0], $order[1]);
                }
            }
            $query->limit(10);
            $promotions = $query->get();
            foreach($promotions as $promo){
                // $promo->images = 'http://34.207.97.193/ahgoo/storage/profile_pics/event_iamge.jpeg';
                try {
                    $promo->formatted_event_date = Carbon::createFromFormat('Y-m-d', $promo->event_date)->format('d M');
                } catch (\Exception $e) {
                    try {
                        $promo->formatted_event_date = Carbon::parse($promo->event_date)->format('d M');
                    } catch (\Exception $e) {
                        $promo->formatted_event_date = 'Invalid date';
                    }
                }
                $promo->users = (object) ['http://34.207.97.193/ahgoo/public/storage/profile_pics/9n4Iib5TeWy4rg7r8ThmHUm68yyXAnKEyeIJRrme.jpg','http://34.207.97.193/ahgoo/public/storage/profile_pics/zvHXOR1FvMfEDAhI7keSGWSSEHQoAR2DqpduS3OL.jpg','http://34.207.97.193/ahgoo/public/storage/profile_pics/aUWcn7KmzHDEckC67yPRCidOrItNY96Hsz19YN8w.jpg'];

                $eventJoinedUserIDs = EventConfirm::where('event_id', $promo->id)->pluck('user_id');
                if (!empty($eventJoinedUserIDs)) {
                    $joinedUsers = AllUser::whereIn('_id', $eventJoinedUserIDs)->get()->map(function ($user) {
                        return [
                            'user_id' => $user->_id,
                            'name' => $user->name,
                            'profile_pic' => $user->profile_pic ?? null
                        ];
                    });
                    $promo->event_joined_users = $joinedUsers;
                } else {
                    $promo->event_joined_users = [];
                }
            }
            return response()->json([
                'status' => true,
                'msg' => 'Recent Events Below',
                'data' => $promotions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Some error occured',
                'data' => (object) []
            ], 500);
        }
    }
    public function my_heritage_events(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $where = [];
            $orderBy = [];
            $where[] = ['is_confirm', '=', '1'];
            if ($request->has('category') && !empty($request->category)) {
                $where[] = ['event_category', '=', $request->category];
            }
            if ($request->has('region') && !empty($request->region)) {
                $all_locations = Locations::where('country', $request->region)->pluck('name')->toArray();
                $where[] = ['location', '=', $all_locations];
            }
            if ($request->has('sort_by')) {
                if($request->sort_by == 'date_asc'){
                    $orderBy[] = ['created_at', 'asc'];
                }else if($request->sort_by == 'date_desc'){
                    $orderBy[] = ['created_at', 'desc'];
                }else if($request->sort_by == 'name_asc'){
                    $orderBy[] = ['event_name', 'asc'];
                }else if($request->sort_by == 'name_desc'){
                    $orderBy[] = ['event_name', 'desc'];
                }else{
                    $orderBy[] = ['created_at', 'desc'];
                }
                $orderBy[] = [$request->order_by_field, $request->order_by_direction];
            } else {
                $orderBy[] = ['created_at', 'desc'];
            }
            // echo '<pre>';print_r($orderBy);exit;
            $query = Events::query();
            foreach ($where as $condition) {
                if (is_array($condition[2])) {
                    // If the condition value is an array, use whereIn
                    $query->whereIn($condition[0], $condition[2]);
                } else {
                    $query->where($condition[0], $condition[1], $condition[2]);
                }
            }
            foreach ($orderBy as $order) {
                if(!empty($order[0])){
                    $query->orderBy($order[0], $order[1]);
                }
            }
            $query->limit(10);
            $promotions = $query->get();
            foreach($promotions as $promo){
                // $promo->images = 'http://34.207.97.193/ahgoo/storage/profile_pics/event_iamge.jpeg';
                try {
                    $promo->formatted_event_date = Carbon::createFromFormat('Y-m-d', $promo->event_date)->format('d M');
                } catch (\Exception $e) {
                    try {
                        $promo->formatted_event_date = Carbon::parse($promo->event_date)->format('d M');
                    } catch (\Exception $e) {
                        $promo->formatted_event_date = 'Invalid date';
                    }
                }
                $promo->users = (object) ['http://34.207.97.193/ahgoo/public/storage/profile_pics/9n4Iib5TeWy4rg7r8ThmHUm68yyXAnKEyeIJRrme.jpg','http://34.207.97.193/ahgoo/public/storage/profile_pics/zvHXOR1FvMfEDAhI7keSGWSSEHQoAR2DqpduS3OL.jpg','http://34.207.97.193/ahgoo/public/storage/profile_pics/aUWcn7KmzHDEckC67yPRCidOrItNY96Hsz19YN8w.jpg'];

                $eventJoinedUserIDs = EventConfirm::where('event_id', $promo->id)->pluck('user_id');
                if (!empty($eventJoinedUserIDs)) {
                    $joinedUsers = AllUser::whereIn('_id', $eventJoinedUserIDs)->get()->map(function ($user) {
                        return [
                            'user_id' => $user->_id,
                            'name' => $user->name,
                            'profile_pic' => $user->profile_pic ?? null
                        ];
                    });
                    $promo->event_joined_users = $joinedUsers;
                } else {
                    $promo->event_joined_users = [];
                }
            }
            return response()->json([
                'status' => true,
                'msg' => 'My Heritage Events Below',
                'data' => $promotions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Some error occured',
                'data' => (object) []
            ], 500);
        }
    }
    public function virtual_ahgoo_events(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $where = [];
            $orderBy = [];
            $where[] = ['is_confirm', '=', '1'];
            $where[] = ['is_virtual', '=', '1'];
            if ($request->has('category') && !empty($request->category)) {
                $where[] = ['event_category', '=', $request->category];
            }
            if ($request->has('region') && !empty($request->region)) {
                $all_locations = Locations::where('country', $request->region)->pluck('name')->toArray();
                $where[] = ['location', '=', $all_locations];
            }
            if ($request->has('sort_by')) {
                if($request->sort_by == 'date_asc'){
                    $orderBy[] = ['created_at', 'asc'];
                }else if($request->sort_by == 'date_desc'){
                    $orderBy[] = ['created_at', 'desc'];
                }else if($request->sort_by == 'name_asc'){
                    $orderBy[] = ['event_name', 'asc'];
                }else if($request->sort_by == 'name_desc'){
                    $orderBy[] = ['event_name', 'desc'];
                }else{
                    $orderBy[] = ['created_at', 'desc'];
                }
                $orderBy[] = [$request->order_by_field, $request->order_by_direction];
            } else {
                $orderBy[] = ['created_at', 'desc'];
            }
            // echo '<pre>';print_r($orderBy);exit;
            $query = Events::query();
            foreach ($where as $condition) {
                if (is_array($condition[2])) {
                    // If the condition value is an array, use whereIn
                    $query->whereIn($condition[0], $condition[2]);
                } else {
                    $query->where($condition[0], $condition[1], $condition[2]);
                }
            }
            foreach ($orderBy as $order) {
                if(!empty($order[0])){
                    $query->orderBy($order[0], $order[1]);
                }
            }
            $query->limit(10);
            $promotions = $query->get();
            foreach($promotions as $promo){
                // $promo->images = 'http://34.207.97.193/ahgoo/storage/profile_pics/event_iamge.jpeg';
                try {
                    $promo->formatted_event_date = Carbon::createFromFormat('Y-m-d', $promo->event_date)->format('d M');
                } catch (\Exception $e) {
                    try {
                        $promo->formatted_event_date = Carbon::parse($promo->event_date)->format('d M');
                    } catch (\Exception $e) {
                        $promo->formatted_event_date = 'Invalid date';
                    }
                }
                $promo->users = (object) ['http://34.207.97.193/ahgoo/public/storage/profile_pics/9n4Iib5TeWy4rg7r8ThmHUm68yyXAnKEyeIJRrme.jpg','http://34.207.97.193/ahgoo/public/storage/profile_pics/zvHXOR1FvMfEDAhI7keSGWSSEHQoAR2DqpduS3OL.jpg','http://34.207.97.193/ahgoo/public/storage/profile_pics/aUWcn7KmzHDEckC67yPRCidOrItNY96Hsz19YN8w.jpg'];

                $eventJoinedUserIDs = EventConfirm::where('event_id', $promo->id)->pluck('user_id');
                if (!empty($eventJoinedUserIDs)) {
                    $joinedUsers = AllUser::whereIn('_id', $eventJoinedUserIDs)->get()->map(function ($user) {
                        return [
                            'user_id' => $user->_id,
                            'name' => $user->name,
                            'profile_pic' => $user->profile_pic ?? null
                        ];
                    });
                    $promo->event_joined_users = $joinedUsers;
                } else {
                    $promo->event_joined_users = [];
                }
            }
            return response()->json([
                'status' => true,
                'msg' => 'Virtual Events Below',
                'data' => $promotions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Some error occured',
                'data' => (object) []
            ], 500);
        }
    }
    public function irl_events(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $where = [];
            $orderBy = [];
            $where[] = ['is_confirm', '=', '1'];
            $where[] = ['is_virtual', '!=', '1'];
            if ($request->has('category') && !empty($request->category)) {
                $where[] = ['event_category', '=', $request->category];
            }
            if ($request->has('region') && !empty($request->region)) {
                $all_locations = Locations::where('country', $request->region)->pluck('name')->toArray();
                $where[] = ['location', '=', $all_locations];
            }
            if ($request->has('sort_by')) {
                if($request->sort_by == 'date_asc'){
                    $orderBy[] = ['created_at', 'asc'];
                }else if($request->sort_by == 'date_desc'){
                    $orderBy[] = ['created_at', 'desc'];
                }else if($request->sort_by == 'name_asc'){
                    $orderBy[] = ['event_name', 'asc'];
                }else if($request->sort_by == 'name_desc'){
                    $orderBy[] = ['event_name', 'desc'];
                }else{
                    $orderBy[] = ['created_at', 'desc'];
                }
                $orderBy[] = [$request->order_by_field, $request->order_by_direction];
            } else {
                $orderBy[] = ['created_at', 'desc'];
            }
            // echo '<pre>';print_r($orderBy);exit;
            $query = Events::query();
            foreach ($where as $condition) {
                if (is_array($condition[2])) {
                    // If the condition value is an array, use whereIn
                    $query->whereIn($condition[0], $condition[2]);
                } else {
                    $query->where($condition[0], $condition[1], $condition[2]);
                }
            }
            foreach ($orderBy as $order) {
                if(!empty($order[0])){
                    $query->orderBy($order[0], $order[1]);
                }
            }
            $query->limit(10);
            $promotions = $query->get();
            foreach($promotions as $promo){
                // $promo->images = 'http://34.207.97.193/ahgoo/storage/profile_pics/event_iamge.jpeg';
                try {
                    $promo->formatted_event_date = Carbon::createFromFormat('Y-m-d', $promo->event_date)->format('d M');
                } catch (\Exception $e) {
                    try {
                        $promo->formatted_event_date = Carbon::parse($promo->event_date)->format('d M');
                    } catch (\Exception $e) {
                        $promo->formatted_event_date = 'Invalid date';
                    }
                }
                $promo->users = (object) ['http://34.207.97.193/ahgoo/public/storage/profile_pics/9n4Iib5TeWy4rg7r8ThmHUm68yyXAnKEyeIJRrme.jpg','http://34.207.97.193/ahgoo/public/storage/profile_pics/zvHXOR1FvMfEDAhI7keSGWSSEHQoAR2DqpduS3OL.jpg','http://34.207.97.193/ahgoo/public/storage/profile_pics/aUWcn7KmzHDEckC67yPRCidOrItNY96Hsz19YN8w.jpg'];

                $eventJoinedUserIDs = EventConfirm::where('event_id', $promo->id)->pluck('user_id');
                if (!empty($eventJoinedUserIDs)) {
                    $joinedUsers = AllUser::whereIn('_id', $eventJoinedUserIDs)->get()->map(function ($user) {
                        return [
                            'user_id' => $user->_id,
                            'name' => $user->name,
                            'profile_pic' => $user->profile_pic ?? null
                        ];
                    });
                    $promo->event_joined_users = $joinedUsers;
                } else {
                    $promo->event_joined_users = [];
                }
            }
            return response()->json([
                'status' => true,
                'msg' => 'IRL Events Below',
                'data' => $promotions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Some error occured',
                'data' => (object) []
            ], 500);
        }
    }
    public function event_details(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|string|max:255',
            'user_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $promo = Promotion::select('_id','post_id','user_id','name_of_audience','created_at','event_location','per_day_spent','total_days')->where('_id',$request->event_id)->first();
            $promo->event_name = $promo->name_of_audience;
            $promo->event_description = 'Come Join Us';
            $post = Posts::where('_id', $promo->post_id)->first();
            if(!isset($post->thumbnail_img) OR empty($post->thumbnail_img)){
                $promo->images = 'http://34.207.97.193/ahgoo/storage/profile_pics/event_iamge.jpeg';
            }else{
                $promo->images = $post->thumbnail_img;
            }
            $promo->formatted_event_date = Carbon::parse($promo->created_at)->format('d M');
            $promo->formatted_event_date_time = Carbon::parse($promo->created_at)->format('d M - H:i');
            $promo->total_amount = $promo->per_day_spent * $promo->total_days;
            $user = AllUser::where('_id', $promo->user_id)->first();
            if(!isset($user->profile_pic) OR empty($user->profile_pic)){
                $user->profile_pic = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
            }
            $promo->name_of_user = $user->name;
            $promo->profile_pic = $user->profile_pic;
            $is_followed = Followers::where('followed_to',$promo->user_id)->where('followed_by',$request->user_id)->first();
            if(!empty($is_followed)){
                $promo->is_already_followed = 1;
            }else{
                $promo->is_already_followed = 0;
            }
            $is_booked = EventConfirm::where('event_id',$promo->_id)->where('user_id',$request->user_id)->first();
            if(!empty($is_booked)){
                $promo->is_already_booked = 1;
            }else{
                $promo->is_already_booked = 0;
            }
            $promo->users = (object) ['http://34.207.97.193/ahgoo/public/storage/profile_pics/9n4Iib5TeWy4rg7r8ThmHUm68yyXAnKEyeIJRrme.jpg','http://34.207.97.193/ahgoo/public/storage/profile_pics/zvHXOR1FvMfEDAhI7keSGWSSEHQoAR2DqpduS3OL.jpg','http://34.207.97.193/ahgoo/public/storage/profile_pics/aUWcn7KmzHDEckC67yPRCidOrItNY96Hsz19YN8w.jpg'];
            return response()->json([
                'status' => true,
                'msg' => 'Recent Events Below',
                'data' => $promo
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Some error occured',
                'data' => (object) []
            ], 500);
        }
    }
    public function event_confirm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'event_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $existingConfirmation = EventConfirm::where([
                ['user_id', '=', $request->user_id],
                ['event_id', '=', $request->event_id]
            ])->first();
            
            if ($existingConfirmation) {
                // The record already exists
                return response()->json([
                    'status' => false,
                    'msg' => 'Event already confirmed',
                    'data' => $existingConfirmation
                ], 422);
            }
            $create = EventConfirm::updateOrCreate([
                'user_id' => $request->user_id,
                'event_id' => $request->event_id
            ]);
            return response()->json([
                'status' => true,
                'msg' => 'Successfully joined the event.',
                'data' => (object) []
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Some error occured',
                'data' => (object) []
            ], 500);
        }
    }
    public function upload_flag_image(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|string|max:255',
            'flag' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'mi_flag' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $updated_data = array();
            $flagPicPath = null;
            if ($request->hasFile('flag')) {
                $flagPicPath = $request->file('flag')->store('flags', 'public');
                $flagPicUrl = Storage::url($flagPicPath);
                $updated_data['flag'] = 'http://34.207.97.193/ahgoo/public'.$flagPicUrl;
            }
            $miflagPicPath = null;
            if ($request->hasFile('mi_flag')) {
                $miflagPicPath = $request->file('mi_flag')->store('flags', 'public');
                $miflagPicUrl = Storage::url($miflagPicPath);
                $updated_data['mi_flag'] = 'http://34.207.97.193/ahgoo/public'.$miflagPicUrl;
            }
            Countries::where('_id', $request->id)->update($updated_data);

            // $token = $user->createToken('api-token')->plainTextToken;
            $user_data = Countries::where('_id', $request->id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Picture Updated Successfully',
                'data' => $user_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function friend_request_list(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $all_list = Friends::where('sent_to', $request->user_id)->where('is_accepted', 0)->orderBy('created_at', 'desc')->get();
            foreach($all_list as $list){
                $details = AllUser::where('_id', $list->sent_by)->first();
                $list->time_ago = Carbon::parse($list->created_at)->diffForHumans();
                $list->user_id = $details->_id;
                $list->name = $details->name;
                $list->email = $details->email;
                $list->username = $details->username;
                $list->phone = $details->phone;
                $list->country = $details->country;
                if(!isset($details->profile_pic) OR empty($details->profile_pic)){
                    $list->profile_pic = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
                }else{
                    $list->profile_pic = $details->profile_pic;
                }
            }
            return response()->json([
                'status' => true,
                'msg' => 'Friend Request List.',
                'data' => $all_list
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Some error occured',
                'data' => (object) []
            ], 500);
        }
    }
    public function pending_friend_request_list(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $all_list = Friends::where('sent_by', $request->user_id)->where('is_accepted', 0)->orderBy('created_at', 'desc')->get();
            foreach($all_list as $list){
                $details = AllUser::where('_id', $list->sent_to)->first();
                $list->time_ago = Carbon::parse($list->created_at)->diffForHumans();
                $list->user_id = $details->_id;
                $list->name = $details->name;
                $list->email = $details->email;
                $list->username = $details->username;
                $list->phone = $details->phone;
                $list->country = $details->country;
                if(!isset($details->profile_pic) OR empty($details->profile_pic)){
                    $list->profile_pic = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
                }else{
                    $list->profile_pic = $details->profile_pic;
                }
            }
            return response()->json([
                'status' => true,
                'msg' => 'Pending Friend Request List.',
                'data' => $all_list
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Some error occured',
                'data' => (object) []
            ], 500);
        }
    }
    public function accept_friend_request(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'accepted_by' => 'required|string|max:255',
            'accepted_to' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $accept = Friends::where('sent_to', $request->accepted_by)->where('sent_by', $request->accepted_to)->update(['is_accepted' => 1]);
            return response()->json([
                'status' => true,
                'msg' => 'Friend Request Accepted Successfully.',
                'data' => (object) []
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Some error occured',
                'data' => (object) []
            ], 500);
        }
    }
    public function reject_friend_request(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rejected_by' => 'required|string|max:255',
            'rejected_to' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $accept = Friends::where('sent_to', $request->rejected_by)->where('sent_by', $request->rejected_to)->update(['is_accepted' => 2]);
            return response()->json([
                'status' => true,
                'msg' => 'Friend Request Rejected Successfully.',
                'data' => (object) []
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Some error occured',
                'data' => (object) []
            ], 500);
        }
    }
    public function delete_friend_request(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'deleted_by' => 'required|string|max:255',
            'deleted_to' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $accept = Friends::where('sent_to', $request->deleted_by)->where('sent_by', $request->deleted_to)->delete();
            return response()->json([
                'status' => true,
                'msg' => 'Friend Request Deleted Successfully.',
                'data' => (object) []
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Some error occured',
                'data' => (object) []
            ], 500);
        }
    }
    public function notification_count(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $all_not = Notifications::where('user_id', $request->user_id)->where('is_seen', 0)->get();
            $friend_not = Notifications::where('user_id', $request->user_id)->where('type', 'friend')->where('is_seen', 0)->get();
            return response()->json([
                'status' => true,
                'msg' => 'Notification Count',
                'all_notification' => COUNT($all_not),
                'freind_request' => COUNT($friend_not),
                'data' => (object) []
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Some error occured',
                'data' => (object) []
            ], 500);
        }
    }
    public function update_profile_summary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'profile_summary' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            AllUser::where('_id', $request->user_id)->update([
                'profile_summary' => $request->profile_summary
            ]);

            // $token = $user->createToken('api-token')->plainTextToken;
            $user_data = AllUser::where('_id', $request->user_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Profile Updated Successfully',
                'data' => $user_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function switch_profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'type' => 'required|in:1,2,3'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            AllUser::where('_id', $request->user_id)->update([
                'user_type' => $request->type
            ]);

            // $token = $user->createToken('api-token')->plainTextToken;
            $user_data = AllUser::where('_id', $request->user_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Profile Switched Successfully',
                'data' => $user_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function create_event_1(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|string|max:255',
            'user_id' => 'required|string|max:255',
            'is_showing_event' => 'required|integer|in:0,1',
            'type' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            if(isset($request->event_type) && !empty($request->event_type)){
                $e_type = $request->event_type;
            }else{
                $e_type = 2;
            }
            $eventData = [
                'user_id' => $request->user_id,
                'post_id' => $request->post_id,
                'is_showing_event' => $request->is_showing_event,
                'type' => $request->type,
                'event_name' => $request->event_name ?? '',
                'event_start' => $request->event_start ?? '',
                'location' => $request->location ?? '',
            ];
        
            $event = Events::updateOrCreate(
                ['_id' => $request->event_id],
                $eventData
            );
        
            $event_id = $event->_id;
            // $token = $user->createToken('api-token')->plainTextToken;
            $promo_data = Events::where('_id', $event_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Event updated successfully',
                'data' => $promo_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function create_event_2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|string|max:255',
            'automatic_public' => 'required|integer|in:0,1'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            Events::where('_id', $request->event_id)->update([
                'automatic_public' => $request->automatic_public
            ]);
            $promo_data = Events::where('_id', $request->event_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Event updated successfully',
                'data' => $promo_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function create_event_budget(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|string|max:255',
            'per_day_spent' => 'required|integer',
            'total_days' => 'required|integer',
            'event_location' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $locationJson = json_encode($request->location);
            Events::where('_id', $request->event_id)->update([
                'per_day_spent' => $request->per_day_spent,
                'total_days' => $request->total_days,
                'event_location' => $request->event_location
            ]);
            $promo_data = Events::where('_id', $request->event_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Event updated successfully',
                'data' => $promo_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function create_events_confirm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|string|max:255',
            'is_confirm' => 'required|integer|in:0,1'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $locationJson = json_encode($request->location);
            Events::where('_id', $request->event_id)->update([
                'is_confirm' => $request->is_confirm
            ]);
            $promo_data = Events::where('_id', $request->event_id)->first();
            $slug = 'event_confirmation';
            $cms_data = Cms::where('slug', 'LIKE', "%{$slug}%")
                        ->first();
            return response()->json([
                'status' => true,
                'msg' => 'Event confirmed successfully',
                'data' => $promo_data,
                'popup_cms_title' => $cms_data->title,
                'popup_cms_content' => $cms_data->content,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function uploadEventCoverImage(Request $request)
    {
        $request->validate([
            'event_id' => 'required|string|max:255',
            'cover_pic' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $event_id = $request->event_id;

        // Store the cover image
        $coverImage = $request->file('cover_pic');
        $path = $coverImage->store('event_media', 'public');
        $thumbPicUrl = Storage::url($path);
        $pth = 'http://34.207.97.193/ahgoo/public'.$thumbPicUrl;

        // Update or create cover image entry in the EventMedia model
        Events::where('_id', $request->event_id)->update([
            'cover_pic' => $pth
        ]);
        $promo_data = Events::where('_id', $request->event_id)->first();
        return response()->json([
            'status' => true,
            'msg' => 'Cover image uploaded successfully',
            'data' => $promo_data
        ], 201);
    }
    public function update_profile_details(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'profile_details' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            AllUser::where('_id', $request->user_id)->update([
                'profile_details' => $request->profile_details,
                'website' => $request->website ?? '',
                'profile_summary' => $request->profile_summary ?? ''
            ]);

            // $token = $user->createToken('api-token')->plainTextToken;
            $user_data = AllUser::where('_id', $request->user_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Profile Updated Successfully',
                'data' => $user_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function create_event_type(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'event_type' => 'required|integer|in:1,2'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $event = Events::create([
                'event_type' => $request->event_type,
                'user_id' => $request->user_id
                // Add any necessary default values for the new Event here
            ]);
            $event_id = $event->_id;
            $promo_data = Events::where('_id', $event_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Event added successfully',
                'data' => $promo_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Addition Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function create_event_slide_2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|string|max:255',
            'event_name' => 'required|string|max:255',
            'event_subtitle' => 'required|string|max:255',
            'event_description' => 'required|string',
            'is_permanent' => 'nullable|in:0,1'
            // 'event_date' => 'required|string|max:255',
            // 'duration' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $eventDate = $request->event_date ?? '';
            $eventEndDate = $request->event_end_date ?? '';

            if (!empty($eventDate)) {
                $eventDate = Carbon::createFromFormat('m-d-Y', $request->event_date)->format('Y-m-d');
            }

            if (!empty($eventEndDate)) {   
            $eventEndDate = Carbon::createFromFormat('m-d-Y', $request->event_end_date)->format('Y-m-d');
            }

            Events::where('_id', $request->event_id)->update([
                'event_name' => $request->event_name,
                'event_subtitle' => $request->event_subtitle,
                'event_description' => $request->event_description,
                'is_permanent' => $request->is_permanent ?? 0,
                'event_date' => $eventDate,
                'event_end_date' => $eventEndDate,
                'duration' => $request->duration ?? ''
            ]);
            $promo_data = Events::where('_id', $request->event_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Event updated successfully',
                'data' => $promo_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Addition Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function create_event_slide_4(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|string|max:255',
            // 'location' => 'required|string|max:255',
            'event_category' => 'required|string',
            'cover_pic' => 'required|string'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            Events::where('_id', $request->event_id)->update([
                'is_virtual' => $request->is_virtual ?? '0',
                'location' => $request->location ?? '',
                'event_category' => $request->event_category,
                'cover_pic' => $request->cover_pic
            ]);
            $promo_data = Events::where('_id', $request->event_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Event updated successfully',
                'data' => $promo_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Addition Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function event_name_checking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_name' => 'required|string|min:5',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json([
                'status' => false,
                'data' => (object) [],
                'msg' => $errors[0]
            ], 422);
        }
        try {
            $event_name = $request->event_name;
            $users = Events::where('event_name', $event_name)
                        ->get();

            // Check if users were found
            if ($users->isEmpty()) {
                return response()->json([
                    'status' => true,
                    'msg' => 'Event Title is available',
                    'data' => (object) []
                ], 200);
            }

            return response()->json([
                'status' => false,
                'msg' => 'Event Title not available.',
                'data' => (object) []
            ], 404);
        }catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Failed!',
                'data' => (object) []
            ], 500);
        }
    }
    public function event_subtitle_checking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_subtitle' => 'required|string|min:5',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json([
                'status' => false,
                'data' => (object) [],
                'msg' => $errors[0]
            ], 422);
        }
        try {
            $event_subtitle = $request->event_subtitle;
            $users = Events::where('event_subtitle', $event_subtitle)
                        ->get();

            // Check if users were found
            if ($users->isEmpty()) {
                return response()->json([
                    'status' => true,
                    'msg' => 'Event Subtitle is available',
                    'data' => (object) []
                ], 200);
            }

            return response()->json([
                'status' => false,
                'msg' => 'Event Subtitle not available.',
                'data' => (object) []
            ], 404);
        }catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Failed!',
                'data' => (object) []
            ], 500);
        }
    }
    public function replaceEventImage(Request $request)
    {
        $request->validate([
            'image_id' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $image_id = $request->image_id;

        // Store the cover image
        $coverImage = $request->file('image');
        $path = $coverImage->store('event_media', 'public');
        $thumbPicUrl = Storage::url($path);
        $pth = 'http://34.207.97.193/ahgoo/public'.$thumbPicUrl;

        // Update or create cover image entry in the EventMedia model
        EventMedia::where('_id', $request->image_id)->update([
            'media_path' => $pth
        ]);
        $promo_data = EventMedia::where('_id', $request->image_id)->first();
        return response()->json([
            'status' => true,
            'msg' => 'Image Replaced Successfully',
            'data' => $promo_data
        ], 201);
    }
    public function my_event_followers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'event_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $followersQuery = Followers::where('followed_to', $request->user_id);

            // Check if the keyword is passed in the request
            if ($request->has('keyword') && !empty($request->keyword)) {
                $keyword = $request->keyword;
                
                // Find users matching the keyword in name, username, or email
                $matchingUsers = AllUser::where('name', 'like', "%$keyword%")
                                        ->orWhere('username', 'like', "%$keyword%")
                                        ->orWhere('email', 'like', "%$keyword%")
                                        ->pluck('_id');
                
                // Filter followers by matching user IDs
                $followersQuery->whereIn('followed_by', $matchingUsers);
            }

            $followers = $followersQuery->orderBy('created_at', 'desc')->get();

            if (!$followers->isEmpty()) {
                foreach ($followers as $follow) {
                    $details = AllUser::where('_id', $follow->followed_by)->first();
                    $follow->_id = $details->_id;
                    $follow->name = $details->name;
                    $followers_total = Followers::where('followed_to', $details->_id)->get();
                    $follow->followers = $followers_total->isEmpty() ? 0 : count($followers_total);
                    $follow->videos = 0;
                    $follow->profile_details = $details->profile_details;
                    $follow->profile_pic = empty($details->profile_pic) ? 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg' : $details->profile_pic;
                    
                    $invites = EventInvites::where('user_id', $details->_id)->where('event_id', $request->event_id)->get();
                    $follow->is_already_invited = $invites->isEmpty() ? 0 : 1;
                }
                
                return response()->json([
                    'status' => true,
                    'msg' => 'Followers below',
                    'data' => $followers
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => 'No Followers Found'
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Please try again later!',
                'data' => (object) []
            ], 500);
        }
    }
    public function my_event_followings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'event_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $followingQuery = Followers::where('followed_by', $request->user_id);

            // Check if the keyword is passed in the request
            if ($request->has('keyword') && !empty($request->keyword)) {
                $keyword = $request->keyword;
                
                // Find users matching the keyword in name, username, or email
                $matchingUsers = AllUser::where('name', 'like', "%$keyword%")
                                        ->orWhere('username', 'like', "%$keyword%")
                                        ->orWhere('email', 'like', "%$keyword%")
                                        ->pluck('_id');
                
                // Filter following by matching user IDs
                $followingQuery->whereIn('followed_to', $matchingUsers);
            }

            $followers = $followingQuery->orderBy('created_at', 'desc')->get();

            if (!$followers->isEmpty()) {
                foreach ($followers as $follow) {
                    $details = AllUser::where('_id', $follow->followed_to)->first();
                    $follow->_id = $details->_id;
                    $follow->name = $details->name;
                    $followers_total = Followers::where('followed_to', $details->_id)->get();
                    $follow->followers = $followers_total->isEmpty() ? 0 : count($followers_total);
                    $follow->videos = 0;
                    $follow->profile_details = $details->profile_details;
                    $follow->profile_pic = empty($details->profile_pic) ? 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg' : $details->profile_pic;
                    
                    $invites = EventInvites::where('user_id', $details->_id)->where('event_id', $request->event_id)->get();
                    $follow->is_already_invited = $invites->isEmpty() ? 0 : 1;
                }
                
                return response()->json([
                    'status' => true,
                    'msg' => 'Following below',
                    'data' => $followers
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => 'No Following Found'
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Please try again later!',
                'data' => (object) []
            ], 500);
        }
    }
    public function my_event_friends(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'event_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $friendsQuery = Friends::where(function($query) use ($request) {
                $query->where('is_accepted', 1)
                        ->where(function($query) use ($request) {
                            $query->where('sent_to', $request->user_id)
                                ->orWhere('sent_by', $request->user_id);
                        });
            });
        
            // Check if the keyword is passed in the request
            if ($request->has('keyword') && !empty($request->keyword)) {
                $keyword = $request->keyword;
                
                // Find users matching the keyword in name, username, or email
                $matchingUsers = AllUser::where('name', 'like', "%$keyword%")
                                        ->orWhere('username', 'like', "%$keyword%")
                                        ->orWhere('email', 'like', "%$keyword%")
                                        ->pluck('_id');
                
                // Filter friends by matching user IDs
                $friendsQuery->where(function($query) use ($matchingUsers) {
                    $query->whereIn('sent_to', $matchingUsers)
                            ->orWhereIn('sent_by', $matchingUsers);
                });
            }
        
            $followers = $friendsQuery->orderBy('created_at', 'desc')
                                        ->get()
                                        ->unique(function ($item) {
                                            return $item['sent_to'] . $item['sent_by'];
                                        })
                                        ->values();
        
            if (!$followers->isEmpty()) {
                foreach ($followers as $follow) {
                    if ($follow->sent_to == $request->user_id) {
                        $details = AllUser::where('_id', $follow->sent_by)->first();
                    } else {
                        $details = AllUser::where('_id', $follow->sent_to)->first();
                    }
                    $follow->_id = $details->_id;
                    $follow->name = $details->name;
                    $followers_total = Followers::where('followed_to', $details->_id)->get();
                    $follow->followers = $followers_total->isEmpty() ? 0 : count($followers_total);
                    $follow->videos = 0;
                    $follow->profile_details = $details->profile_details;
                    $follow->profile_pic = empty($details->profile_pic) ? 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg' : $details->profile_pic;
        
                    $invites = EventInvites::where('user_id', $details->_id)->where('event_id', $request->event_id)->get();
                    $follow->is_already_invited = $invites->isEmpty() ? 0 : 1;
                }
        
                return response()->json([
                    'status' => true,
                    'msg' => 'Friends below',
                    'data' => $followers
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => 'No Friends Found'
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Please try again later!',
                'data' => (object) []
            ], 500);
        }
    }
    public function my_event_all_inv(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'event_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $user_id = $request->user_id;
            $keyword = $request->keyword;

            // Function to get user details and followers count
            $getUserDetails = function ($user) use ($request) {
                $user_details = AllUser::where('_id', $user)->first();
                $followers_total = Followers::where('followed_to', $user_details->_id)->count();
                return [
                    '_id' => $user_details->_id,
                    'name' => $user_details->name,
                    'followers' => $followers_total,
                    'videos' => 0,
                    'profile_details' => $user_details->profile_details,
                    // 'profile_pic' => $user_details->profile_pic ?? 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg',
                    'profile_pic' => empty($user_details->profile_pic) ? 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg' : $user_details->profile_pic,
                    'is_already_invited' => EventInvites::where('user_id', $user_details->_id)->where('event_id', $request->event_id)->exists() ? 1 : 0,
                ];
            };

            // Query for followers
            $followersQuery = Followers::where('followed_to', $user_id);

            // Query for followings
            $followingsQuery = Followers::where('followed_by', $user_id);

            // Query for friends
            $friendsQuery = Friends::where(function($query) use ($user_id) {
                $query->where('is_accepted', 1)
                    ->where(function($query) use ($user_id) {
                        $query->where('sent_to', $user_id)
                                ->orWhere('sent_by', $user_id);
                    });
            });

            // Apply keyword search to all queries if keyword is provided
            if (!empty($keyword)) {
                $matchingUsers = AllUser::where('name', 'like', "%$keyword%")
                                        ->orWhere('username', 'like', "%$keyword%")
                                        ->orWhere('email', 'like', "%$keyword%")
                                        ->pluck('_id');

                $followersQuery->whereIn('followed_by', $matchingUsers);
                $followingsQuery->whereIn('followed_to', $matchingUsers);
                $friendsQuery->where(function($query) use ($matchingUsers) {
                    $query->whereIn('sent_to', $matchingUsers)
                        ->orWhereIn('sent_by', $matchingUsers);
                });
            }

            // Get followers, followings, and friends
            $followers = $followersQuery->orderBy('created_at', 'desc')->get();
            $followings = $followingsQuery->orderBy('created_at', 'desc')->get();
            $friends = $friendsQuery->orderBy('created_at', 'desc')
                                    ->get()
                                    ->unique(function ($item) {
                                        return $item['sent_to'] . $item['sent_by'];
                                    })
                                    ->values();

            // Compile all details
            $allConnections = [];

            foreach ($followers as $follower) {
                $allConnections[] = $getUserDetails($follower->followed_by);
            }

            foreach ($followings as $following) {
                $allConnections[] = $getUserDetails($following->followed_to);
            }

            foreach ($friends as $friend) {
                $user_id_to_get_details = ($friend->sent_to == $user_id) ? $friend->sent_by : $friend->sent_to;
                $allConnections[] = $getUserDetails($user_id_to_get_details);
            }

            // Remove duplicate entries by _id
            $allConnections = collect($allConnections)->unique('_id')->values();

            return response()->json([
                'status' => true,
                'msg' => 'All connections below',
                'data' => $allConnections
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Please try again later!',
                'data' => (object) []
            ], 500);
        }
    }
    public function sent_event_invite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|string|max:255',
            'user_id' => 'required|array',
            'user_id.*' => 'required|string|max:255',
            'invited_by' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $allErrors = [];

            foreach ($errors as $messageArray) {
                $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
            }

            $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma

            return response()->json([
                'status' => false,
                'data' => (object) [],
                'msg' => $formattedErrors
            ], 422);
        }

        try {
            $userIds = $request->user_id;
            $event_id = $request->event_id;
            $invited_by = $request->invited_by;
            $user = AllUser::where('_id', $invited_by)->first();

            foreach ($userIds as $user_id) {
                // Check if the invite already exists
                $existingInvite = EventInvites::where('event_id', $event_id)
                    ->where('user_id', $user_id)
                    ->where('invited_by', $invited_by)
                    ->first();

                if ($existingInvite) {
                    continue; // Skip if invite already exists
                }

                // Create event invite
                EventInvites::create([
                    'event_id' => $event_id,
                    'user_id' => $user_id,
                    'invited_by' => $invited_by
                ]);

                // Create notification
                Notifications::create([
                    'user_id' => $user_id,
                    'relavant_id' => $event_id,
                    'relavant_image' => 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg',
                    'message' => $user->name . ' sent you a event invite',
                    'type' => 'event',
                    'is_seen' => 0
                ]);
            }
            $getAllInvites = EventInvites::where('event_id',$request->event_id)->get();
            $user_invites_id = $getAllInvites[0]->user_id;
            $user_invites = AllUser::where('_id', $user_invites_id)->first();

            return response()->json([
                'status' => true,
                'msg' => 'Invited Successfully',
                'total_invites_msg' => $user_invites->name . ' and '.COUNT($getAllInvites).' people were invited.',
                'data' => (object) []
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Please try again later!',
                'data' => (object) []
            ], 500);
        }
    }
    public function event_all_details(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $event_details = Events::where('_id',$request->event_id)->first();
            if(!empty($event_details->audience_location)){
                $event_details->audience_location = json_decode($event_details->audience_location);
            }

            if(!empty($event_details->event_date)){
                try{
                    $event_details->event_date_formatted = Carbon::createFromFormat('m-d-Y', $event_details->event_date)->format('d M');
                }catch(Exception $e){
                    $event_details->event_date_formatted = Carbon::createFromFormat('Y-m-d', $event_details->event_date)->format('d M');
                }
            }else{
                $event_details->event_date_formatted = null;
            }

            $user = AllUser::where('_id',$event_details->user_id)->first();
            $event_details->event_created_by = $user->name;

            $followers_total = Followers::where('followed_to',$event_details->user_id)->get();
            if(!empty($followers_total)){
                $event_details->event_created_by_followers = count($followers_total);
            }else{
                $event_details->event_created_by_followers = 0;
            }
            if(!isset($user->profile_pic) OR empty($user->profile_pic)){
                $event_details->event_created_by_profile_pic = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
            }else{
                $event_details->event_created_by_profile_pic = $user->profile_pic;
            }
            $event_details->total_amount = number_format($event_details->total_amount);
            $inv_cnt = EventInvites::where('event_id',$request->event_id)->get();
            if(!$inv_cnt->isEmpty()){
                $event_details->event_invites_count = count($inv_cnt);
                $event_details->event_invites1 = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
                $event_details->event_invites2 = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
            }else{
                $event_details->event_invites_count = 0;
                $event_details->event_invites1 = '';
                $event_details->event_invites2 = '';
            }
            $event_details->event_created_by = $user->name;
            $event_details->event_created_by = $user->name;
            if(!empty($request->event_id)){
                $is_booked = EventConfirm::where('event_id',$request->event_id)->where('user_id',$request->user_id)->first();
                if(!empty($is_booked)){
                    $event_details->is_already_booked = 1;
                }else{
                    $event_details->is_already_booked = 0;
                }
            }else{
                $event_details->is_already_booked = 0;
            }

            $eventJoinedUserIDs = EventConfirm::where('event_id', $request->event_id)->pluck('user_id');
            if(!empty($eventJoinedUserIDs)){
                $joinedUsers = AllUser::whereIn('_id', $eventJoinedUserIDs)->get()->map(function($user){
                    return [
                        'user_id' => $user->_id,
                        'name' => $user->name,
                        'profile_pic' => $user->profile_pic ?? null
                    ];
                });
                $event_details->event_joined_users = $joinedUsers;
            } else {
                $event_details->event_joined_users = [];
            }


            $event_details->is_bookmarked = BookmarkEvent::where('event_id', $request->event_id)->where('user_id', $request->user_id)->exists() ? 1 : 0;

            if($event_details->is_permanent != 1){
                if(!empty($event_details->event_end_date)){
                    $event_details->event_date_range = $event_details->event_date.' to '.$event_details->event_end_date;
                }else{
                    $event_details->event_date_range = $event_details->event_date;
                }
            }else{
                $event_details->event_date_range = '';
            }
            
            $slug = 'delete_event';
            $cms_data = Cms::where('slug', 'LIKE', "%{$slug}%")
                        ->first();
            $event_details->delete_cms_title = $cms_data->title;
            $event_details->delete_cms_content = $cms_data->content;
            $event_details->event_media = EventMedia::select('_id','media_path')->where('event_id',$request->event_id)->limit(5)->orderBy('created_at', 'desc')->get();
            return response()->json([
                'status' => true,
                'msg' => 'Event Details Below',
                'data' => $event_details
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Some error occured',
                'data' => (object) []
            ], 500);
        }
    }
    function event_edit_information(Request $request){
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|string|max:255',
            'event_name' => 'required|string|max:255',
            'event_description' => 'required|string',
            'cover_pic' => 'required|string',
            'event_date' => 'nullable|date|after_or_equal:today|max:255',
            'event_end_date' => 'nullable|date|after_or_equal:event_date|max:255',
            // 'duration' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            Events::where('_id', $request->event_id)->update([
                'event_name' => $request->event_name,
                'event_description' => $request->event_description,
                'cover_pic' => $request->cover_pic,
                'event_date' => $request->event_date ?? '',
                'event_end_date' => $request->event_end_date ?? '',
                'duration' => $request->duration ?? ''
            ]);
            $promo_data = Events::where('_id', $request->event_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Event updated successfully',
                'data' => $promo_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Addition Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function delete_event(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|string|max:255',
            'user_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $event_delete = Events::where('_id', $request->event_id)
                       ->where('user_id', $request->user_id)
                       ->delete();
            if($event_delete){
                EventMedia::where('event_id', $request->event_id)->delete();
                EventInvites::where('event_id', $request->event_id)->delete();
            }
            return response()->json([
                'status' => true,
                'msg' => 'Event deleted successfully',
                'data' => (object) []
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function paid_events_slide_4(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|string|max:255',
            'automatic_public' => 'nullable|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            Events::where('_id', $request->event_id)->update([
                'automatic_public' => $request->automatic_public ?? 0,
                'is_name_public_already_created' => $request->automatic_public ?? 0
            ]);
            $promo_data = Events::where('_id', $request->event_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Event updated successfully',
                'data' => $promo_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function paid_event_create_audience(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|string|max:255',
            'estimated_size' => 'required|string|max:255',
            'name_of_audience' => 'required|string|max:255',
            'age_from' => 'required|integer',
            'age_to' => 'required|integer',
            'gender' => 'required|string|max:255',
            'audience_location' => 'required|array'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $locationJson = json_encode($request->audience_location);
            Events::where('_id', $request->event_id)->update([
                'estimated_size' => $request->estimated_size,
                'name_of_audience' => $request->name_of_audience,
                'age_from' => $request->age_from,
                'age_to' => $request->age_to,
                'gender' => $request->gender,
                'audience_location' => $locationJson
            ]);
            $promo_data = Events::where('_id', $request->event_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Promotion updated successfully',
                'data' => $promo_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function paid_event_audience_name_check(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_of_audience' => 'required|string|min:5',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json([
                'status' => false,
                'data' => (object) [],
                'msg' => $errors[0]
            ], 422);
        }
        try {
            $name_of_audience = $request->name_of_audience;

            // Search for users where name, email, or username contains the keyword
            $users = Events::where('name_of_audience', 'LIKE', "%{$name_of_audience}%")
                        ->get();

            // Check if users were found
            if ($users->isEmpty()) {
                return response()->json([
                    'status' => true,
                    'msg' => 'Audience name is available',
                    'data' => (object) []
                ], 200);
            }

            return response()->json([
                'status' => false,
                'msg' => 'Audience name not available.',
                'data' => (object) []
            ], 404);
        }catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Failed!',
                'data' => (object) []
            ], 500);
        }
    }
    public function paid_events_about_the_atendees(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|string|max:255',
            'create_group_chat' => 'nullable|in:0,1',
            'show_my_website' => 'nullable|in:0,1'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            Events::where('_id', $request->event_id)->update([
                'create_group_chat' => $request->create_group_chat ?? 0,
                'show_my_website' => $request->show_my_website ?? 0,
                'web_address' => $request->web_address ?? ''
            ]);
            $promo_data = Events::where('_id', $request->event_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Event updated successfully',
                'data' => $promo_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function paid_events_add_web_address(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|string|max:255',
            'web_address' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $web_address = $request->web_address;

            // Search for users where name, email, or username contains the keyword
            $users = Events::where('web_address', 'LIKE', "%{$web_address}%")
                        ->get();

            // Check if users were found
            if ($users->isEmpty()) {
                Events::where('_id', $request->event_id)->update([
                    'web_address' => $request->web_address
                ]);
                $promo_data = Events::where('_id', $request->event_id)->first();
                return response()->json([
                    'status' => true,
                    'msg' => 'Web address added',
                    'data' => $promo_data
                ], 200);
            }

            return response()->json([
                'status' => false,
                'msg' => 'Web address already used.',
                'data' => (object) []
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function paid_event_budget(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|string|max:255',
            'per_day_spent' => 'required|integer',
            'total_days' => 'required|integer'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            Events::where('_id', $request->event_id)->update([
                'per_day_spent' => $request->per_day_spent,
                'total_days' => $request->total_days,
                'total_amount' => $request->per_day_spent * $request->total_days
            ]);
            $promo_data = Events::where('_id', $request->event_id)->first();
            $promo_data->total_amount = number_format($promo_data->total_amount);
            return response()->json([
                'status' => true,
                'msg' => 'Event updated successfully',
                'data' => $promo_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function paid_event_payment_method(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|string|max:255',
            'payment_method' => 'required|integer'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            Events::where('_id', $request->event_id)->update([
                'payment_method' => $request->payment_method
            ]);
            $promo_data = Events::where('_id', $request->event_id)->first();
            $slug = 'paid_event_cofirm';
            $cms_data = Cms::where('slug', 'LIKE', "%{$slug}%")
                        ->first();
            return response()->json([
                'status' => true,
                'msg' => 'Event updated successfully',
                'data' => $promo_data,
                'popup_cms_title' => $cms_data->title,
                'popup_cms_content' => $cms_data->content
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    function test(Request $request){
        $name = $request->name;
        return response()->json([
            'status' => true,
            'msg' => 'Hello '.$name.'! Welcome to the site.'
        ], 200);
    }
    public function categories_event_wise(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $events = Events::select('event_category','cover_pic')->groupBy('event_category')->where('is_confirm','1')->get();
            return response()->json([
                'status' => true,
                'msg' => 'Event Category Listing',
                'data' => $events
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Addition Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function events_by_category(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'category' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $events = Events::where('event_category',$request->category)->where('is_confirm','1')->get();
            return response()->json([
                'status' => true,
                'msg' => 'Event Listing',
                'data' => $events
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Addition Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function my_active_events(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            if($request->order == 'default'){
                $promotions = Events::where('is_confirm', '1')
                ->where('user_id', $request->user_id)
                ->where(function ($query) {
                    $query->where(function ($query) {
                            // Regular expression to match the 'YYYY-MM-DD' format
                            $query->where('event_end_date', 'regex', '/^\d{4}-\d{2}-\d{2}$/')
                                ->where('event_end_date', '>', date('Y-m-d'));
                        });
                })
                ->orderBy('created_at', 'desc')
                ->limit(15)
                ->get();
                
            }else if($request->order == 'older'){
                $promotions = Events::where('is_confirm', '1')
                                ->where('user_id', $request->user_id)
                                ->where(function ($query) {
                                    $query->where(function ($query) {
                                            // Regular expression to match the 'YYYY-MM-DD' format
                                            $query->where('event_end_date', 'regex', '/^\d{4}-\d{2}-\d{2}$/')
                                                ->where('event_end_date', '>', date('Y-m-d'));
                                        });
                                })
                                ->orderBy('created_at', 'asc')
                                ->limit(15)
                                ->get();
            }else{
                $promotions = Events::where('is_confirm', '1')
                                ->where('user_id', $request->user_id)
                                ->where(function ($query) {
                                    $query->where(function ($query) {
                                            // Regular expression to match the 'YYYY-MM-DD' format
                                            $query->where('event_date', 'regex', '/^\d{4}-\d{2}-\d{2}$/')
                                                ->where('event_date', '>=', date('Y-m-d'));
                                        });
                                })
                                ->orderBy('created_at', 'desc')
                                ->limit(15)
                                ->get();
            }
            
            foreach($promotions as $promo){
                // $promo->images = 'http://34.207.97.193/ahgoo/storage/profile_pics/event_iamge.jpeg';
                try {
                    $promo->formatted_event_date = Carbon::createFromFormat('Y-m-d', $promo->event_date)->format('d M');
                } catch (\Exception $e) {
                    try {
                        $promo->formatted_event_date = Carbon::parse($promo->event_date)->format('d M');
                    } catch (\Exception $e) {
                        $promo->formatted_event_date = 'Invalid date';
                    }
                }
                $promo->users = (object) ['http://34.207.97.193/ahgoo/public/storage/profile_pics/9n4Iib5TeWy4rg7r8ThmHUm68yyXAnKEyeIJRrme.jpg','http://34.207.97.193/ahgoo/public/storage/profile_pics/zvHXOR1FvMfEDAhI7keSGWSSEHQoAR2DqpduS3OL.jpg','http://34.207.97.193/ahgoo/public/storage/profile_pics/aUWcn7KmzHDEckC67yPRCidOrItNY96Hsz19YN8w.jpg'];
                $promo->users_counts = rand(10, 20);

                $eventJoinedUserIDs = EventConfirm::where('event_id', $promo->id)->pluck('user_id');
                if (!empty($eventJoinedUserIDs)) {
                    $joinedUsers = AllUser::whereIn('_id', $eventJoinedUserIDs)->get()->map(function ($user) {
                        return [
                            'user_id' => $user->_id,
                            'name' => $user->name,
                            'profile_pic' => $user->profile_pic ?? null
                        ];
                    });
                    $promo->event_joined_users = $joinedUsers;
                } else {
                    $promo->event_joined_users = [];
                }
            }
            return response()->json([
                'status' => true,
                'msg' => 'My Active Events Below',
                'data' => $promotions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Some error occured',
                'data' => (object) []
            ], 500);
        }
    }
    public function my_finished_events(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            if($request->order == 'default'){
                $promotions = Events::where('is_confirm', '1')
                ->where('user_id', $request->user_id)
                ->where(function ($query) {
                    // Regular expression to match the 'YYYY-MM-DD' format
                    $query->where('event_date', 'regex', '/^\d{4}-\d{2}-\d{2}$/')
                        ->where('event_date', '<=', date('Y-m-d'));
                })
                ->orderBy('created_at', 'desc')
                ->limit(15)
                ->get();
                
            }else if($request->order == 'older'){
                $promotions = Events::where('is_confirm', '1')
                                ->where('user_id', $request->user_id)
                                ->where(function ($query) {
                                    // Regular expression to match the 'YYYY-MM-DD' format
                                    $query->where('event_date', 'regex', '/^\d{4}-\d{2}-\d{2}$/')
                                        ->where('event_date', '<=', date('Y-m-d'));
                                })
                                ->orderBy('created_at', 'asc')
                                ->limit(15)
                                ->get();
            }else{
                $promotions = Events::where('is_confirm', '1')
                                ->where('user_id', $request->user_id)
                                ->where(function ($query) {
                                    // Regular expression to match the 'YYYY-MM-DD' format
                                    $query->where('event_date', 'regex', '/^\d{4}-\d{2}-\d{2}$/')
                                        ->where('event_date', '<=', date('Y-m-d'));
                                })
                                ->orderBy('created_at', 'desc')
                                ->limit(15)
                                ->get();
            }
            
            foreach($promotions as $promo){
                // $promo->images = 'http://34.207.97.193/ahgoo/storage/profile_pics/event_iamge.jpeg';
                try {
                    $promo->formatted_event_date = Carbon::createFromFormat('Y-m-d', $promo->event_date)->format('d M');
                } catch (\Exception $e) {
                    try {
                        $promo->formatted_event_date = Carbon::parse($promo->event_date)->format('d M');
                    } catch (\Exception $e) {
                        $promo->formatted_event_date = 'Invalid date';
                    }
                }
                $promo->users = (object) ['http://34.207.97.193/ahgoo/public/storage/profile_pics/9n4Iib5TeWy4rg7r8ThmHUm68yyXAnKEyeIJRrme.jpg','http://34.207.97.193/ahgoo/public/storage/profile_pics/zvHXOR1FvMfEDAhI7keSGWSSEHQoAR2DqpduS3OL.jpg','http://34.207.97.193/ahgoo/public/storage/profile_pics/aUWcn7KmzHDEckC67yPRCidOrItNY96Hsz19YN8w.jpg'];
                $promo->users_counts = rand(10, 20);

                $eventJoinedUserIDs = EventConfirm::where('event_id', $promo->id)->pluck('user_id');
                if (!empty($eventJoinedUserIDs)) {
                    $joinedUsers = AllUser::whereIn('_id', $eventJoinedUserIDs)->get()->map(function ($user) {
                        return [
                            'user_id' => $user->_id,
                            'name' => $user->name,
                            'profile_pic' => $user->profile_pic ?? null
                        ];
                    });
                    $promo->event_joined_users = $joinedUsers;
                } else {
                    $promo->event_joined_users = [];
                }
            }
            return response()->json([
                'status' => true,
                'msg' => 'My Active Events Below',
                'data' => $promotions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Some error occured',
                'data' => (object) []
            ], 500);
        }
    }
    public function update_preferred_countries(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'countries_suggestions' => 'required|array'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $customJson = json_encode($request->countries_suggestions);

            $promo_data = PreferredSuggestions::updateOrCreate(
                ['user_id' => $request->user_id],
                ['countries_suggestions' => $customJson]
            );
            // $promo_data = PreferredSuggestions::where('user_id', $request->user_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Suggestions updated successfully',
                'data' => $promo_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function update_preferred_interest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'interests_suggestions' => 'required|array'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $customJson = json_encode($request->interests_suggestions);

            $promo_data = PreferredSuggestions::updateOrCreate(
                ['user_id' => $request->user_id],
                ['interests_suggestions' => $customJson]
            );
            // $promo_data = PreferredSuggestions::where('user_id', $request->user_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Suggestions updated successfully',
                'data' => $promo_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function update_preferred_age_group(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'age_groups_suggestions' => 'required|array'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $customJson = json_encode($request->age_groups_suggestions);

            $promo_data = PreferredSuggestions::updateOrCreate(
                ['user_id' => $request->user_id],
                ['age_groups_suggestions' => $customJson]
            );
            // $promo_data = PreferredSuggestions::where('user_id', $request->user_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Suggestions updated successfully',
                'data' => $promo_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function get_states_by_country(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'country_name' => 'required|array'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $promo_data = AllLocations::select('city', 'county', 'country')
                                        ->whereIn('country', $request->country_name)
                                        ->where('county', '!=', '')
                                        ->groupBy('county')
                                        ->limit(100)
                                        ->get();
            // $promo_data = PreferredSuggestions::where('user_id', $request->user_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'States List Below',
                'data' => $promo_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function update_preferred_states(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'states_suggestions' => 'required|array'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $customJson = json_encode($request->states_suggestions);

            $promo_data = PreferredSuggestions::updateOrCreate(
                ['user_id' => $request->user_id],
                ['states_suggestions' => $customJson]
            );
            // $promo_data = PreferredSuggestions::where('user_id', $request->user_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Suggestions updated successfully',
                'data' => $promo_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function public_for_audience_list(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $return_array = [];
            $name_public = Events::select('_id','estimated_size','name_of_audience','age_from','age_to','gender','audience_location')
                    ->where('user_id', $request->user_id)
                    ->where('name_of_audience','!=','')
                    ->whereNotNull('name_of_audience')
                    // ->where('is_confirm','1')
                    ->get();
            if($name_public->isEmpty()){
                return response()->json([
                    'status' => true,
                    'msg' => 'No list found',
                    'data' => (object) []
                ], 200);
            }else{
                foreach($name_public as $list){
                    $list->audience_location = json_decode($list->audience_location);
                    if(isset($list->name_of_audience) && !empty($list->name_of_audience)){
                        $return_array[] = $list;
                    }
                }
                return response()->json([
                    'status' => true,
                    'msg' => 'Name public list follows',
                    'data' => $return_array
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function name_public_select_for_event(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'event_id' => 'required|string|max:255',
            'name_public_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $return_array = array();
            $name_public = Events::where('_id', $request->name_public_id)->first();

            Events::where('_id', $request->event_id)->update([
                'estimated_size' => $name_public->estimated_size,
                'name_of_audience' => $name_public->name_of_audience,
                'age_from' => $name_public->age_from,
                'age_to' => $name_public->age_to,
                'gender' => $name_public->gender,
                'audience_location' => $name_public->audience_location
            ]);
            $promo_data = Events::where('_id', $request->event_id)->first();

            return response()->json([
                'status' => true,
                'msg' => 'Name public updated for the event',
                'data' => $promo_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function events_search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|string|max:255',
            'keyword' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $eventObj = Events::where('is_confirm', '1');
            
            if (!empty($request->user_id)) {
                $eventObj = $eventObj->where('user_id', $request->user_id);
            }
            
            if (!empty($request->keyword)) {
                $eventObj = $eventObj->where('event_name', 'LIKE', "%{$request->keyword}%");
            }
            $promotions = $eventObj->orderBy('created_at', 'desc')->limit(20)->get();

            foreach($promotions as $promo){
                // $promo->images = 'http://34.207.97.193/ahgoo/storage/profile_pics/event_iamge.jpeg';
                try {
                    $promo->formatted_event_date = Carbon::createFromFormat('Y-m-d', $promo->event_date)->format('d M');
                } catch (\Exception $e) {
                    try {
                        $promo->formatted_event_date = Carbon::parse($promo->event_date)->format('d M');
                    } catch (\Exception $e) {
                        $promo->formatted_event_date = 'Invalid date';
                    }
                }
                $promo->users = (object) ['http://34.207.97.193/ahgoo/public/storage/profile_pics/9n4Iib5TeWy4rg7r8ThmHUm68yyXAnKEyeIJRrme.jpg','http://34.207.97.193/ahgoo/public/storage/profile_pics/zvHXOR1FvMfEDAhI7keSGWSSEHQoAR2DqpduS3OL.jpg','http://34.207.97.193/ahgoo/public/storage/profile_pics/aUWcn7KmzHDEckC67yPRCidOrItNY96Hsz19YN8w.jpg'];
            }
            return response()->json([
                'status' => true,
                'msg' => 'Events Below',
                'data' => $promotions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Some error occured',
                'data' => (object) []
            ], 500);
        }
    }
    public function my_regions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $return_array = array();
            $user = AllUser::where('_id', $request->user_id)->first();
            if(!empty($user->country)){
                array_push($return_array,$user->country);
            }
            if(!empty($user->country1)){
                array_push($return_array,$user->country1);
            }
            if(!empty($user->country2)){
                array_push($return_array,$user->country);
            }
            if(!empty($user->country3)){
                array_push($return_array,$user->country3);
            }
            if(!empty($user->country4)){
                array_push($return_array,$user->country4);
            }
            if(!empty($user->country5)){
                array_push($return_array,$user->country5);
            }
            $return_array = array_unique($return_array);
            return response()->json([
                'status' => true,
                'msg' => 'My Regions below',
                'data' => $return_array
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Some error occured',
                'data' => (object) []
            ], 500);
        }
    }
    public function event_confirm_attendies(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $followers = EventConfirm::where('event_id',$request->event_id)->groupBy('user_id')->get();
            // echo '<pre>';print_r($followers);exit;
            if(!empty($followers)){
                foreach($followers as $follow){
                    $details = AllUser::where('_id', $follow->user_id)->first();
                    $follow->_id = $details->_id;
                    $follow->name = $details->name;
                    $follow->email = $details->email;
                    $follow->username = $details->username;
                    $follow->phone = $details->phone;
                    $follow->country = $details->country;
                    $follow->user_type = $details->user_type;
                    $followers_total = Followers::where('followed_to',$details->_id)->get();
                    if(!empty($followers_total)){
                        $follow->followers = count($followers_total);
                    }else{
                        $follow->followers = 0;
                    }
                    $follow->post = 0;
                    $followed_total = Followers::where('followed_by',$details->_id)->get();
                    if(!empty($followed_total)){
                        $follow->followed = count($followed_total);
                    }else{
                        $follow->followed = 0;
                    }
                    $follow->friends = 0;
                    $follow->videos = 0;
                    $follow->amount1 = '0$';
                    $follow->amount2 = '0$';
                    $follow->account_description = 'Love Yourself';
                    if(!isset($follow->profile_pic) OR empty($follow->profile_pic)){
                        $follow->profile_pic = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
                    }
                    $country =  $details->country;
                    $country_details = Countries::where('name', $country)->first();
                    if(!empty($country_details)){
                        $follow->country_code = $country_details->phone_code;
                        $follow->country_flag = $country_details->flag;
                    }else{
                        $follow->country_code = '';
                        $follow->country_flag = '';
                    }
                }
                return response()->json([
                    'status' => true,
                    'msg' => 'Event Confirms Attendies Below',
                    'data' => $followers
                ], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => 'No Event Confirms Attendies Found'
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Please try again later!',
                'data' => (object) []
            ], 500);
        }
    }
    public function public_for_audience_list_for_promotion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $return_array = array();
            $name_public = Promotion::select('_id','estimated_size','name_of_audience','age_from','age_to','gender','location')
                    ->where('user_id', $request->user_id)
                    ->where('name_of_audience','!=','')
                    ->where('is_confirm','1')->get();
            if($name_public->isEmpty()){
                return response()->json([
                    'status' => true,
                    'msg' => 'No list found',
                    'data' => (object) []
                ], 200);
            }else{
                foreach($name_public as $list){
                    $list->location = json_decode($list->location);
                    if(isset($list->name_of_audience) && !empty($list->name_of_audience)){
                        array_push($return_array,$list);
                    }
                }
                return response()->json([
                    'status' => true,
                    'msg' => 'Name public list follows',
                    'data' => $return_array
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function name_public_select_for_promotion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'promotion_id' => 'required|string|max:255',
            'name_public_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $return_array = array();
            $name_public = Promotion::where('_id', $request->name_public_id)->first();

            Promotion::where('_id', $request->promotion_id)->update([
                'estimated_size' => $name_public->estimated_size,
                'name_of_audience' => $name_public->name_of_audience,
                'age_from' => $name_public->age_from,
                'age_to' => $name_public->age_to,
                'gender' => $name_public->gender,
                'location' => $name_public->location
            ]);
            $promo_data = Promotion::where('_id', $request->promotion_id)->first();

            return response()->json([
                'status' => true,
                'msg' => 'Name public updated for the promotion',
                'data' => $promo_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function get_cities_by_country(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'country_name' => 'required|array'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $promo_data = AllLocations::select('city', 'county', 'country')
                                        ->whereIn('country', $request->country_name)
                                        ->where('city', '!=', '')
                                        ->groupBy('city')
                                        ->limit(100)
                                        ->get();
            // $promo_data = PreferredSuggestions::where('user_id', $request->user_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Cities List Below',
                'data' => $promo_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function update_preferred_cities(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'cities_suggestions' => 'required|array'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            $customJson = json_encode($request->cities_suggestions);

            $promo_data = PreferredSuggestions::updateOrCreate(
                ['user_id' => $request->user_id],
                ['cities_suggestions' => $customJson]
            );
            // $promo_data = PreferredSuggestions::where('user_id', $request->user_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Suggestions updated successfully',
                'data' => $promo_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Updation Failed',
                'data' => (object) []
            ], 500);
        }
    }
    public function get_user_by_mobile_or_email(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_no' => 'nullable|required_without:email_id|digits:10',
            'email_id' => 'nullable|required_without:phone_no|email',
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $allErrors = [];
            
                foreach ($errors as $messageArray) {
                    $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                }
            
                $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma
                
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => $formattedErrors
                ], 422);
            }
        }

        try {
            if ($request->filled('phone_no')) {
                $user = AllUser::where('phone', $request->phone_no)->first();
            } elseif ($request->filled('email_id')) {
                $user = AllUser::where('email', $request->email_id)->first();
            }
            if ($user) {
                // Add the mi_flag key to the response
                $user->mi_flag = isset($user->profile_pic) ? $user->profile_pic : 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
                return response()->json([
                    'status' => true,
                    'msg' => 'User Found!',
                    'data' => $user
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'msg' => 'No user found!',
                    'data' => (object) []
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Please try again later!',
                'data' => (object) []
            ], 500);
        }
    }

    public function bookmark_event(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|string|max:255',
                'event_ids' => 'required|array',
            ]);

            if ($validator->fails()) {
                if ($validator->fails()) {
                    $errors = $validator->errors()->toArray();
                    $allErrors = [];

                    foreach ($errors as $messageArray) {
                        $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                    }

                    $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma

                    return response()->json([
                        'status' => false,
                        'data' => (object) [],
                        'msg' => $formattedErrors
                    ], 422);
                }
            }

            $eventIDs = $request->event_ids;
            $userID = $request->user_id;

            $bookmarkedEvent = BookmarkEvent::where('user_id', $userID)->delete();

            foreach ($eventIDs as $eventID) {
                $bookmarkedEvent = new BookmarkEvent();
                $bookmarkedEvent->event_id = $eventID;
                $bookmarkedEvent->user_id = $userID;
                $bookmarkedEvent->save();
            }

            return response()->json([
                'status' => true,
                'msg' => 'Bookmarked Events Updated.',
                'data' => (object) []
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Please try again later!',
                'data' => (object) []
            ], 500);
        }
    }

    public function undo_bookmark_event(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|string|max:255',
                'event_id' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                if ($validator->fails()) {
                    $errors = $validator->errors()->toArray();
                    $allErrors = [];

                    foreach ($errors as $messageArray) {
                        $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                    }

                    $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma

                    return response()->json([
                        'status' => false,
                        'data' => (object) [],
                        'msg' => $formattedErrors
                    ], 422);
                }
            }

            $eventID = $request->event_id;
            $userID = $request->user_id;
            
            BookmarkEvent::where('user_id', $userID)->where('event_id', $eventID)->delete();

            return response()->json([
                'status' => true,
                'msg' => 'Event Unbookmarked.',
                'data' => (object) []
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Please try again later!',
                'data' => (object) []
            ], 500);
        }
    }
    
    public function get_bookmark_event(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                if ($validator->fails()) {
                    $errors = $validator->errors()->toArray();
                    $allErrors = [];

                    foreach ($errors as $messageArray) {
                        $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
                    }

                    $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma

                    return response()->json([
                        'status' => false,
                        'data' => (object) [],
                        'msg' => $formattedErrors
                    ], 422);
                }
            }

            $userID = $request->user_id;

            $bookmarkedEvents = BookmarkEvent::where('user_id', $userID)->get();

            $eventDetails = [];

            foreach ($bookmarkedEvents as $bookmark) {
                // Fetch the full event details for each bookmarked event
                $event = Events::find($bookmark->event_id);
                $eventDetails[] = $event;
            }

            return response()->json([
                'status' => true,
                'msg' => 'Bookmarked Events.',
                'data' => $eventDetails
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Please try again later!',
                'data' => (object) []
            ], 500);
        }
    }

    public function get_user_selections(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'type' => 'required|integer|in:1,2,3,4', // type must be one of 1, 2, 3, or 4
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $allErrors = [];

            foreach ($errors as $messageArray) {
                $allErrors = array_merge($allErrors, $messageArray); // Merge all error messages into a single array
            }

            $formattedErrors = implode(' ', $allErrors); // Join all error messages with a comma

            return response()->json([
                'status' => false,
                'data' => (object) [],
                'msg' => $formattedErrors
            ], 422);
        }

        try {
            // Fetch user profile based on user_id
            $user = AllUser::find($request->user_id);

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'msg' => 'User not found!',
                    'data' => (object) []
                ], 404);
            }

            // Fetch the user's preferred selections from the 'preferred_suggestions' collection
            $userPreferences = PreferredSuggestions::where('user_id', $request->user_id)->first();

            if (!$userPreferences) {
                return response()->json([
                    'status' => false,
                    'msg' => 'No preferences found for this user.',
                    'data' => (object) []
                ], 404);
            }

            // Check 'type' and return appropriate data
            $responseData = [];

            switch ($request->type) {
                case 1:
                    // Return countries suggestions
                    $responseData = $userPreferences->countries_suggestions ?? [];
                    break;

                case 2:
                    // Return cities suggestions
                    $responseData = $userPreferences->cities_suggestions ?? [];
                    break;

                case 3:
                    // Return interests suggestions
                    $responseData = $userPreferences->interests_suggestions ?? [];
                    break;

                case 4:
                    // Return age group suggestions
                    $responseData = $userPreferences->age_groups_suggestions ?? [];
                    break;

                default:
                    return response()->json([
                        'status' => false,
                        'msg' => 'Invalid type parameter.',
                        'data' => (object) []
                    ], 400);
            }

            if (is_string($responseData)) {
                $responseData = json_decode($responseData);
            }
            
            return response()->json([
                'status' => true,
                'msg' => 'User selections fetched successfully.',
                'data' => $responseData,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'An error occurred, please try again later.',
                'data' => (object) []
            ], 500);
        }
    }
}
