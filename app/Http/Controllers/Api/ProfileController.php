<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AllUser;
use App\Models\Followers;
use App\Models\Countries;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

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
            $user->followers = 0; // Replace with your actual method to get followers
            $user->post = 0; // Replace with your actual method to get followers
            $user->followed = 0; // Replace with your actual method to get followers
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
}