<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AllUser;
use App\Models\Followers;
use App\Models\Friends;
use App\Models\Blocks;
use App\Models\Countries;
use App\Models\Notifications;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
            $user->profile_pic = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';

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
                    $follow->profile_pic = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
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
                    $follow->profile_pic = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
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
            $user->profile_pic = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';

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
}