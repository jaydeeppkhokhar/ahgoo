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
            // if($request->order == 'old'){
            //     $followers = Followers::where('followed_to', $request->user_id)->orderBy('created_at', 'asc')->get();
            // }else if($request->order == 'recent'){
            //     $followers = Followers::where('followed_to', $request->user_id)->orderBy('created_at', 'desc')->get();
            // }else{
            //     $followers = Followers::where('followed_to',$request->user_id)->get();
            // }
            // echo '<pre>';print_r($followers);exit;
            // if(!empty($followers)){
            //     foreach($followers as $follow){
            //         $details = AllUser::where('_id', $follow->followed_by)->first();
            //         $follow->_id = $details->_id;
            //         $follow->name = $details->name;
            //         $follow->email = $details->email;
            //         $follow->username = $details->username;
            //         $follow->phone = $details->phone;
            //         $follow->country = $details->country;
            //         $follow->user_type = $details->user_type;
            //         $followers_total = Followers::where('followed_to',$details->_id)->get();
            //         if(!empty($followers_total)){
            //             $follow->followers = count($followers_total);
            //         }else{
            //             $follow->followers = 0;
            //         }
            //         $follow->post = 0;
            //         $follow->followed = 0;
            //         $follow->friends = 0;
            //         $follow->videos = 0;
            //         $follow->amount1 = '0$';
            //         $follow->amount2 = '0$';
            //         $follow->account_description = 'Love Yourself';
            //         $follow->profile_pic = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
            //         $country =  $details->country;
            //         $country_details = Countries::where('name', $country)->first();
            //         if(!empty($country_details)){
            //             $follow->country_code = $country_details->phone_code;
            //             $follow->country_flag = $country_details->flag;
            //         }else{
            //             $follow->country_code = '';
            //             $follow->country_flag = '';
            //         }
            //     }
            //     return response()->json([
            //         'status' => true,
            //         'msg' => 'Follower below',
            //         'data' => $followers
            //     ], 200);
            // }else{
                return response()->json([
                    'status' => false,
                    'data' => (object) [],
                    'msg' => 'No Followers Found'
                ], 422);
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
            $user_data = Posts::where('user_id', $request->user_id)->get();
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
                    $thumbnail_img = $post->thumbnail_img
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
            $promotion = Promotion::create([
                'user_id' => $request->user_id,
                'post_id' => $request->post_id,
                'is_showing_event' => $request->is_showing_event,
                'type' => $request->type
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
    public function my_posts(Request $request)
    {
        if(!empty($request->user_id)){
            $posts = Posts::where('user_id', $request->user_id)->get();
            if ($posts->isEmpty()) {
                return response()->json([
                    'status' => false,
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
}