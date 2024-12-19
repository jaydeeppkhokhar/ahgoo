<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AllUser;
use App\Models\Followers;
use App\Models\Hobbies;
use App\Models\EventCategories;
use App\Models\InfluencerCat;
use App\Models\Locations;
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
        // try {
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
                if(!isset($usr->profile_pic) OR empty($usr->profile_pic)){
                    $usr->profile_pic = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
                }
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
        // }catch (\Exception $e) {
        //     return response()->json([
        //         'status' => false,
        //         'msg' => 'Failed!',
        //         'data' => (object) []
        //     ], 500);
        // }
    }
    public function suggestions(Request $request)
    {
        if (empty($request->user_id)) {
            return response()->json([
                'status' => false,
                'msg' => 'Please provide user id.',
                'data' => (object) []
            ], 422);
        }

        // Fetch all users except the one with the specified user_id
        $users = AllUser::where('_id', '!=', $request->user_id)->get();

        if ($users->isEmpty()) {
            return response()->json([
                'status' => false,
                'msg' => "No Suggestions Found.",
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
            'msg' => 'All suggestions.',
            'data' => $users
        ], 200);
    }

    public function people_near_you(Request $request)
    {
        if (empty($request->user_id)) {
            return response()->json([
                'status' => false,
                'msg' => 'Please provide user id.',
                'data' => (object) []
            ], 422);
        }

        // Fetch the current user to get their country
        $myself = AllUser::find($request->user_id);

        if (!$myself) {
            return response()->json([
                'status' => false,
                'msg' => 'User not found.',
                'data' => (object) []
            ], 404);
        }

        $country = $myself->country;

        // Fetch users from the same country, excluding the current user
        $users = AllUser::where('_id', '!=', $request->user_id)
                        ->where('country', $country)
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();

        if ($users->isEmpty()) {
            return response()->json([
                'status' => false,
                'msg' => "No nearby people found.",
                'data' => (object) []
            ], 401);
        }

        // Fetch all necessary follower relationships in a single query
        $userIds = $users->pluck('_id')->toArray();
        $followers = Followers::whereIn('followed_to', $userIds)->get();
        $followedByUser = Followers::where('followed_by', $request->user_id)
                                   ->whereIn('followed_to', $userIds)
                                   ->get();
        $followedByCounts = Followers::whereIn('followed_by', $userIds)
                                     ->get()
                                     ->groupBy('followed_by')
                                     ->map->count();

        // Pre-compute follow statuses and counts
        $followerCounts = $followers->groupBy('followed_to')->map->count();
        $isFollowedByUser = $followedByUser->pluck('followed_to')->toArray();

        foreach ($users as $user) {
            $user->followers = $followerCounts[$user->_id] ?? 0;
            $user->followed = $followedByCounts[$user->_id] ?? 0;
            $user->is_already_followed = in_array($user->_id, $isFollowedByUser) ? 1 : 0;
            $user->is_already_freind = 0; // Update as needed
            $user->videos = 0; // Replace with actual method to get videos
            $user->account_description = 'Love Yourself'; // Replace with your actual method to get account description
            if(!isset($user->profile_pic) OR empty($user->profile_pic)){
            $user->profile_pic = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
            }
        }

        return response()->json([
            'status' => true,
            'msg' => 'Nearby People.',
            'data' => $users
        ], 200);
    }

    public function country_of_origin(Request $request)
    {
        if (empty($request->user_id)) {
            return response()->json([
                'status' => false,
                'msg' => 'Please provide user id.',
                'data' => (object) []
            ], 422);
        }

        // Fetch the current user to get their country
        $myself = AllUser::find($request->user_id);

        if (!$myself) {
            return response()->json([
                'status' => false,
                'msg' => 'User not found.',
                'data' => (object) []
            ], 404);
        }

        $country = $myself->country;

        // Fetch users from the same country, excluding the current user
        $users = AllUser::where('_id', '!=', $request->user_id)
                        ->where('country', $country)
                        ->orderBy('created_at', 'asc')
                        ->limit(10)
                        ->get();

        if ($users->isEmpty()) {
            return response()->json([
                'status' => false,
                'msg' => "No people found.",
                'data' => (object) []
            ], 401);
        }

        // Fetch all necessary follower relationships in a single query
        $userIds = $users->pluck('_id')->toArray();
        $followers = Followers::whereIn('followed_to', $userIds)->get();
        $followedByUser = Followers::where('followed_by', $request->user_id)
                                   ->whereIn('followed_to', $userIds)
                                   ->get();
        $followedByCounts = Followers::whereIn('followed_by', $userIds)
                                     ->get()
                                     ->groupBy('followed_by')
                                     ->map->count();

        // Pre-compute follow statuses and counts
        $followerCounts = $followers->groupBy('followed_to')->map->count();
        $isFollowedByUser = $followedByUser->pluck('followed_to')->toArray();

        foreach ($users as $user) {
            $user->followers = $followerCounts[$user->_id] ?? 0;
            $user->followed = $followedByCounts[$user->_id] ?? 0;
            $user->is_already_followed = in_array($user->_id, $isFollowedByUser) ? 1 : 0;
            $user->is_already_freind = 0; // Update as needed
            $user->videos = 0; // Replace with actual method to get videos
            $user->account_description = 'Love Yourself'; // Replace with your actual method to get account description
            if(!isset($user->profile_pic) OR empty($user->profile_pic)){
            $user->profile_pic = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
            }
        }

        return response()->json([
            'status' => true,
            'msg' => 'People found.',
            'data' => $users
        ], 200);
    }

    public function influencers(Request $request)
    {
        if(!empty($request->user_id)){
            $myself = AllUser::where('_id', $request->user_id)->first();
            // $country = $myself->country;
            // $user = AllUser::where('_id', '!=', $request->user_id)->where('country',$country)->orderBy('created_at', 'desc')->limit(5)->get();
            $user = AllUser::where('_id', '!=', $request->user_id)
                ->where('user_type', 2)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            foreach ($user as $usr) {
                // Assuming you have methods to fetch followers and videos for a user
                $followers_total = Followers::where('followed_to',$usr->_id)->get();
                if(!empty($followers_total)){
                    $usr->followers = count($followers_total);
                }else{
                    $usr->followers = 0;
                }
                $user->post = 0; // Replace with your actual method to get followers
                $followed_total = Followers::where('followed_by',$usr->_id)->get();
                if(!empty($followed_total)){
                    $usr->followed = count($followed_total);
                }else{
                    $usr->followed = 0;
                }
                $is_followed = Followers::where('followed_to',$usr->_id)->where('followed_by',$request->user_id)->first();
                if(!empty($is_followed)){
                    $usr->is_already_followed = 1;
                }else{
                    $usr->is_already_followed = 0;
                }
                $usr->is_already_freind = 0;
                $usr->videos = 0; // Replace with your actual method to get videos
                $usr->account_description = 'Love Yourself'; // Replace with your actual method to get videos
                if(!isset($user->profile_pic) OR empty($user->profile_pic)){
                $usr->profile_pic = 'http://34.207.97.193/ahgoo/storage/profile_pics/no_image.jpg';
                }
            }
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'msg' => "No influencers found.",
                    'data' => (object) []
                ], 401);
            }
            return response()->json([
                'status' => true,
                'msg' => 'All Influencers.',
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
    public function hobbies(Request $request){
        $hobbies = Hobbies::get();
        if (!$hobbies) {
            return response()->json([
                'status' => false,
                'msg' => "No Hobbies found.",
                'data' => (object) []
            ], 401);
        }
        return response()->json([
            'status' => true,
            'msg' => 'Hobbies Listing.',
            'data' => $hobbies
        ], 200);
    }
    public function influencer_categories(Request $request){
        $infcat = InfluencerCat::get();
        if (!$infcat) {
            return response()->json([
                'status' => false,
                'msg' => "No Category found.",
                'data' => (object) []
            ], 401);
        }
        return response()->json([
            'status' => true,
            'msg' => 'Category Listing.',
            'data' => $infcat
        ], 200);
    }
    public function all_locations(Request $request){
        $locations = Locations::get();
        if (!$locations) {
            return response()->json([
                'status' => false,
                'msg' => "No Locations found.",
                'data' => (object) []
            ], 401);
        }
        return response()->json([
            'status' => true,
            'msg' => 'Location Listing.',
            'data' => $locations
        ], 200);
    }
    
    public function locations_search(Request $request){
        if (empty($request->keyword)) {
            return response()->json([
                'status' => false,
                'msg' => 'Please provide keyword.',
                'data' => (object) []
            ], 422);
        }else{
            $locations = Locations::where('name','LIKE', "%{$request->keyword}%")->get();
            if (!$locations) {
                return response()->json([
                    'status' => false,
                    'msg' => "No Locations found.",
                    'data' => (object) []
                ], 401);
            }
            return response()->json([
                'status' => true,
                'msg' => 'Location Listing.',
                'data' => $locations
            ], 200);
        }
        
    }
    public function eventCategories(Request $request){
        $categories = EventCategories::get();
        if (!$categories) {
            return response()->json([
                'status' => false,
                'msg' => "No Event Categories found.",
                'data' => (object) []
            ], 401);
        }
        return response()->json([
            'status' => true,
            'msg' => 'Event Categories Listing.',
            'data' => $categories
        ], 200);
    }
}
