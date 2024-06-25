<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Countries;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class CountriesController extends Controller
{
    public function getAllCountries(Request $request)
    {
        $countries = Countries::get();
        return response()->json([
            'status' => true,
            'msg' => 'Countries List Follows',
            'data' => $countries
        ], 200);
    }
}