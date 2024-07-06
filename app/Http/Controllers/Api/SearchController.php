<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AllUser;
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
}
