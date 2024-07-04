<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\AllUser;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'phone' => 'required|min:10|numeric|unique:phone',
            'password' => 'required|string|min:8',
            // // 'dob' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            // 'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
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
            // Handle the profile picture upload
            $profilePicPath = null;
            if ($request->hasFile('profile_pic')) {
                $profilePicPath = $request->file('profile_pic')->store('profile_pics', 'public');
                $profilePicUrl = Storage::url($profilePicPath);
            }

            $user = AllUser::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'phone' => $request->phone,
                'country' => $request->country,
                'password' => Hash::make($request->password),
                // 'profile_pic' => $profilePicUrl,
            ]);

            // $token = $user->createToken('api-token')->plainTextToken;
            $user_data = AllUser::where('email', $request->email)->first();
            return response()->json([
                'status' => true,
                'msg' => 'User registered successfully',
                'data' => $user_data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Registration failed',
                'data' => (object) []
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json([
                'status' => false,
                'msg' => 'Invalid',
                'data' => $errors
            ], 422);
        }
        if(!empty($request->email)){
            $user = AllUser::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Invalid credentials',
                    'data' => (object) []
                ], 401);
            }

            // $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'status' => true,
                'msg' => 'Login successful',
                'data' => $user
            ], 200);
        }
        else if(!empty($request->phone)){
            $user = AllUser::where('phone', $request->phone)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Invalid credentials',
                    'data' => (object) []
                ], 401);
            }

            // $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'status' => true,
                'msg' => 'Login successful',
                'data' => $user
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'msg' => 'Please provide either email or phone.',
                'data' => (object) []
            ], 422);
        }
        
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out'], 200);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
    public function username_checkups(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|min:5',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json([
                'status' => false,
                'data' => array(),
                'msg' => $errors[0]
            ], 422);
        }
        try {
            $username = $request->username;

            // Search for users where name, email, or username contains the keyword
            $users = AllUser::where('username', 'LIKE', "%{$username}%")
                        ->get();

            // Check if users were found
            if ($users->isEmpty()) {
                return response()->json([
                    'status' => true,
                    'msg' => 'Username is available',
                    'data' => (object) []
                ], 200);
            }

            return response()->json([
                'status' => false,
                'msg' => 'Username not available.',
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
    public function email_checkups(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
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
            $email = $request->email;

            // Search for users where name, email, or username contains the keyword
            $users = AllUser::where('email', 'LIKE', "%{$email}%")
                        ->get();

            // Check if users were found
            if ($users->isEmpty()) {
                return response()->json([
                    'status' => true,
                    'msg' => 'Email is available',
                    'data' => (object) []
                ], 200);
            }

            return response()->json([
                'status' => false,
                'msg' => 'Email already used.',
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
}
