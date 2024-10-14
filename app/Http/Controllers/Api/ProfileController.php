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
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

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
            if($request->type == 'all'){
                $notifications = Notifications::where('user_id', $request->user_id)
                                            ->where('created_at', '>=', $sevenDaysAgo)
                                            ->where('is_seen', 1)
                                            ->get();
            }else{
                // $notifications = Notifications::where('user_id',$request->user_id)->where('type',$request->type)->get();
                $notifications = Notifications::where('user_id', $request->user_id)
                                            ->where('type',$request->type)
                                            ->where('is_seen', 1)
                                            ->where('created_at', '>=', $sevenDaysAgo)
                                            ->get();
            }
            if(!empty($notifications)){
                foreach($notifications as $not){
                    $details = AllUser::where('_id', $not->relavant_id)->first();
                    $not->relavant_name = $details->name;
                }
            }
            $new_notifications = Notifications::where('user_id', $request->user_id)
                                                ->where('is_seen', 0)
                                                ->get();
            if(!empty($new_notifications)){
                foreach($new_notifications as $noti){
                    $details = AllUser::where('_id', $noti->relavant_id)->first();
                    $noti->relavant_name = $details->name;
                }
            }
            if ($notifications->isEmpty()) {
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
    public function create_promotion_1(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'post_id' => 'required|string|max:255',
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
            $promotion = Promotion::create([
                'user_id' => $request->user_id,
                'post_id' => $request->post_id,
                'is_showing_event' => $request->is_showing_event,
                'type' => $request->type,
                'event_type' => $e_type
            ]);
            $insertedId = $promotion->_id;
            // $token = $user->createToken('api-token')->plainTextToken;
            $promo_data = Promotion::where('_id', $insertedId)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Promotion added successfully',
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
    public function create_promotion_2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'promotion_id' => 'required|string|max:255',
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
            Promotion::where('_id', $request->promotion_id)->update([
                'automatic_public' => $request->automatic_public
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
            Promotion::where('_id', $request->promotion_id)->update([
                'per_day_spent' => $request->per_day_spent,
                'total_days' => $request->total_days,
                'event_location' => $request->event_location
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
    public function create_promotion_confirm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'promotion_id' => 'required|string|max:255',
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
    public function uploadEventImages(Request $request)
    {
        $request->validate([
            'images' => 'required|array|size:5',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
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
            $promotions = Promotion::select('_id','post_id','name_of_audience','created_at','event_location')->orderBy('created_at', 'desc')->limit(8)->get();
            foreach($promotions as $promo){
                $promo->event_name = $promo->name_of_audience;
                $promo->event_description = 'Come Join Us';
                $post = Posts::where('_id', $promo->post_id)->first();
                if(!isset($post->thumbnail_img) OR empty($post->thumbnail_img)){
                    $promo->images = 'http://34.207.97.193/ahgoo/storage/profile_pics/event_iamge.jpeg';
                }else{
                    $promo->images = $post->thumbnail_img;
                }
                // $promo->images = 'http://34.207.97.193/ahgoo/storage/profile_pics/event_iamge.jpeg';
                $promo->formatted_event_date = Carbon::parse($promo->created_at)->format('d M');
                // $promo->users = AllUser::whereNotNull('profile_pic')
                //                 ->inRandomOrder()
                //                 ->take(4)
                //                 ->get();
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
            $promotions = Promotion::select('_id','post_id','name_of_audience','created_at','event_location')->orderBy('created_at', 'desc')->limit(8)->get();
            foreach($promotions as $promo){
                $promo->event_name = $promo->name_of_audience;
                $promo->event_description = 'Come Join Us';
                $post = Posts::where('_id', $promo->post_id)->first();
                if(!isset($post->thumbnail_img) OR empty($post->thumbnail_img)){
                    $promo->images = 'http://34.207.97.193/ahgoo/storage/profile_pics/event_iamge.jpeg';
                }else{
                    $promo->images = $post->thumbnail_img;
                }
                $promo->formatted_event_date = Carbon::parse($promo->created_at)->format('d M');
                // $promo->users = AllUser::whereNotNull('profile_pic')
                //                 ->inRandomOrder()
                //                 ->take(4)
                //                 ->get();
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
            $promotions = Promotion::select('_id','post_id','name_of_audience','created_at','event_location')->orderBy('created_at', 'desc')->limit(8)->get();
            foreach($promotions as $promo){
                $promo->event_name = $promo->name_of_audience;
                $promo->event_description = 'Come Join Us';
                $post = Posts::where('_id', $promo->post_id)->first();
                if(!isset($post->thumbnail_img) OR empty($post->thumbnail_img)){
                    $promo->images = 'http://34.207.97.193/ahgoo/storage/profile_pics/event_iamge.jpeg';
                }else{
                    $promo->images = $post->thumbnail_img;
                }
                $promo->formatted_event_date = Carbon::parse($promo->created_at)->format('d M');
                // $promo->users = AllUser::whereNotNull('profile_pic')
                //                 ->inRandomOrder()
                //                 ->take(4)
                //                 ->get();
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
            $create = EventConfirm::create([
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
            return response()->json([
                'status' => true,
                'msg' => 'Event confirmed successfully',
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
                'website' => $request->website ?? ''
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

}