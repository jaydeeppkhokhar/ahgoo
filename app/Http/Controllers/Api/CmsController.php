<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cms;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class CmsController extends Controller
{
    public function getcmsdata(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required|string|min:3',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json([
                'status' => false,
                'data' => array(),
                'msg' => $errors
            ], 422);
        }
        try {
            $slug = $request->slug;

            // Search for users where name, email, or username contains the keyword
            $cms_data = Cms::where('slug', 'LIKE', "%{$slug}%")
                        ->get();

            return response()->json([
                'status' => true,
                'msg' => 'Details Below',
                'data' => $cms_data
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
