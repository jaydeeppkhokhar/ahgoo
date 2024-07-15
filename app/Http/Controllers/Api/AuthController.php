<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\AllUser;
use App\Models\Otp;
use App\Models\Countries;
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
            'phone' => 'required|digits:10|numeric|unique:phone',
            'password' => 'required|string|min:8',
            // // 'dob' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            // 'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            // $errors = $validator->errors()->all();
            // $errors = $validator->errors()->toArray();
            // $formattedErrors = [];
            // foreach ($errors as $field => $messageArray) {
            //     $formattedErrors[$field] = $messageArray[0]; // Get the first error message
            // }
            
            // return response()->json([
            //     'status' => false,
            //     'data' => (object) [],
            //     'msg' => $formattedErrors
            // ], 422);
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
                'is_loggedin' => 0,
                'data' => $errors
            ], 422);
        }
        if(!empty($request->email)){
            $user = AllUser::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Invalid credentials',
                    'is_loggedin' => 0,
                    'data' => (object) []
                ], 401);
            }

            // $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'status' => true,
                'msg' => 'Login successful',
                'is_loggedin' => 1,
                'data' => $user
            ], 200);
        }
        else if(!empty($request->phone)){
            $user = AllUser::where('phone', $request->phone)->where('country', $request->country)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Invalid credentials',
                    'is_loggedin' => 0,
                    'data' => (object) []
                ], 401);
            }

            // $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'status' => true,
                'msg' => 'Login successful',
                'is_loggedin' => 1,
                'data' => $user
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'msg' => 'Please provide either email or phone.',
                'is_loggedin' => 0,
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
                'data' => (object) [],
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
    public function updateCountry()
    {
        // AllUser::query()->update(['country' => 'India']);
        AllUser::query()->update(['user_type' => 1]);
        return response()->json(['message' => 'Country updated to India for all users']);
    }
    public function forget_password(Request $request)
    {
        if(!empty($request->email)){
            $user = AllUser::where('email', $request->email)->first();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'msg' => "The e-mail ".$request->email." was not found. Try another phone number or email address. If you don't have an Ahgoo account you can register.",
                    'data' => (object) []
                ], 401);
            }
            $country =  $user->country;
            $country_details = Countries::where('name', $country)->first();

            $user->country_code = $country_details->phone_code;
            // $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'status' => true,
                'msg' => 'User details below',
                'data' => $user
            ], 200);
        }
        else if(!empty($request->phone)){
            if(!empty($request->country)){
                $user = AllUser::where('phone', $request->phone)->where('country', $request->country)->first();

                if (!$user) {
                    return response()->json([
                        'status' => false,
                        'msg' => "The phone no ".$request->phone." was not found. Try another phone number or email address. If you don't have an Ahgoo account you can register.",
                        'data' => (object) []
                    ], 401);
                }

                // $token = $user->createToken('api-token')->plainTextToken;

                return response()->json([
                    'status' => true,
                    'msg' => 'User details below',
                    'data' => $user
                ], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'msg' => 'Please select country.',
                    'data' => (object) []
                ], 422);
            }
        }else{
            return response()->json([
                'status' => false,
                'msg' => 'Please provide either email or phone.',
                'data' => (object) []
            ], 422);
        }
        
    }
    public function send_otp(Request $request)
    {
        if(!empty($request->email)){
            $user = AllUser::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'msg' => "The e-mail ".$request->email." was not found. Try another phone number or email address. If you don't have an Ahgoo account you can register.",
                    'data' => (object) []
                ], 401);
            }
            $deletedRows = Otp::where('user_id', $user->_id)->delete();
            $send_otp = Otp::create([
                'user_id' => $user->_id,
                'type' => '1',
                'details' => $request->email,
                'otp' => '6563'
            ]);
            // $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'status' => true,
                'msg' => 'OTP Send Successfully. Please check your email.',
                'data' => $user
            ], 200);
        }
        else if(!empty($request->phone)){
            $user = AllUser::where('phone', $request->phone)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'msg' => "The phone no ".$request->phone." was not found. Try another phone number or email address. If you don't have an Ahgoo account you can register.",
                    'data' => (object) []
                ], 401);
            }
            $deletedRows = Otp::where('user_id', $user->_id)->delete();
            $send_otp = Otp::create([
                'user_id' => $user->_id,
                'type' => '2',
                'details' => $request->phone,
                'otp' => '6563'
            ]);
            // $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'status' => true,
                'msg' => 'OTP Send Successfully. Please check your mobile.',
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
    public function verify_otp(Request $request)
    {
        if(!empty($request->user_id)){
            if(!empty($request->otp)){
                $user = AllUser::where('_id', $request->user_id)->first();
                $otp = Otp::where('user_id', $request->user_id)->where('otp', $request->otp)->first();
                if (!$user || !$otp) {
                    return response()->json([
                        'status' => false,
                        'msg' => "Try Again with a valid code.",
                        'data' => (object) []
                    ], 401);
                }
                $deletedRows = Otp::where('user_id', $user->_id)->delete();
                return response()->json([
                    'status' => true,
                    'msg' => 'Verified code.',
                    'data' => $user
                ], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'msg' => 'Please provide OTP.',
                    'data' => (object) []
                ], 422);
            }
        }else{
            return response()->json([
                'status' => false,
                'msg' => 'Please provide user id.',
                'data' => (object) []
            ], 422);
        }
        
    }
    public function change_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
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
                'password' => Hash::make($request->password)
            ]);
            $user_data = AllUser::where('_id', $request->user_id)->first();
            return response()->json([
                'status' => true,
                'msg' => 'Password changed successfully',
                'data' => $user_data
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
