<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AllUser;
use App\Models\Followers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'keyword' => 'required|string|min:3',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json([
                'status' => false,
                'data' => (object) [],
                'msg' => $errors
            ], 422);
        }
        try {
            $keyword = $request->keyword;

            // Search for users where name, email, or username contains the keyword
            $users = AllUser::where('name', 'LIKE', "%{$keyword}%")
                        ->orWhere('email', 'LIKE', "%{$keyword}%")
                        ->orWhere('username', 'LIKE', "%{$keyword}%")
                        ->get();

            // Check if users were found
            foreach ($users as $usr) {
                // Assuming you have methods to fetch followers and videos for a user
                $usr->followers = 0; // Replace with your actual method to get followers
                $usr->videos = 0; // Replace with your actual method to get videos
                $usr->account_description = 'Love Yourself'; // Replace with your actual method to get videos
                $usr->profile_pic = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
            }
            if ($users->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'No users found',
                    'data' => (object) []
                ], 404);
            }

            return response()->json([
                'status' => true,
                'msg' => 'Users found',
                'data' => $users
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Failed!',
                'data' => (object) []
            ], 500);
        }
    }
    public function suggestions(Request $request)
    {
        if(!empty($request->user_id)){
            $user = AllUser::where('_id', '!=', $request->user_id)->get();
            foreach ($user as $usr) {
                $is_followed = Followers::where('followed_to',$usr->_id)->where('followed_by',$request->user_id)->first();
                if(!empty($is_followed)){
                    $usr->is_already_followed = 1;
                }else{
                    $usr->is_already_followed = 0;
                }
                $usr->is_already_freind = 0;
                // Assuming you have methods to fetch followers and videos for a user
                $followers_total = Followers::where('followed_to',$usr->_id)->get();
                if(!empty($followers_total)){
                    $usr->followers = count($followers_total);
                }else{
                    $usr->followers = 0;
                }
                $usr->videos = 0;
                $usr->freinds = 0;
                $usr->account_description = 'Love Yourself'; // Replace with your actual method to get videos
                $usr->profile_pic = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
            }
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'msg' => "No Suggestions Found.",
                    'data' => (object) []
                ], 401);
            }
            return response()->json([
                'status' => true,
                'msg' => 'All suggestions.',
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
    public function people_near_you(Request $request)
    {
        if(!empty($request->user_id)){
            $myself = AllUser::where('_id', $request->user_id)->first();
            $country = $myself->country;
            $user = AllUser::where('_id', '!=', $request->user_id)->where('country',$country)->get();
            foreach ($user as $usr) {
                // Assuming you have methods to fetch followers and videos for a user
                $usr->followers = 0; // Replace with your actual method to get followers
                $usr->videos = 0; // Replace with your actual method to get videos
                $usr->account_description = 'Love Yourself'; // Replace with your actual method to get videos
                $usr->profile_pic = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
            }
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'msg' => "No nearby people found.",
                    'data' => (object) []
                ], 401);
            }
            return response()->json([
                'status' => true,
                'msg' => 'Nearby Peoples.',
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
}
