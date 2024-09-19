<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
// use App\Helpers\AgoraTokenGenerator;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

include_once(app_path('Services/Agora/RtcTokenBuilder2.php'));

class AgoraTokenController extends Controller
{
    public function generateToken(Request $request)
    {
        $appId = 'd260338840b74397920cefbbaeed2b94';//env("AGORA_APP_ID");
        $appCertificate = '0c6676f808e44defac84eb03e888e9dd';//env("AGORA_APP_CERTIFICATE");
        $channelName = $request->input('channel_name');
        $uid = $request->input('uid');
        $role = $request->input('role', \RtcTokenBuilder2::ROLE_PUBLISHER); // Default to publisher
        $tokenExpirationInSeconds = $request->input('token_expiration', 3600); // Default to 1 hour
        $privilegeExpirationInSeconds = $request->input('privilege_expiration', 3600); // Default to 1 hour

        if (empty($appId) || empty($appCertificate)) {
            return response()->json([
                'status' => false,
                'msg' => 'AGORA_APP_ID and AGORA_APP_CERTIFICATE must be set in environment variables',
            ], 500);
        }

        $token = \RtcTokenBuilder2::buildTokenWithUid(
            $appId,
            $appCertificate,
            $channelName,
            $uid,
            $role,
            $tokenExpirationInSeconds,
            $privilegeExpirationInSeconds
        );

        return response()->json([
            'status' => true,
            'token' => $token,
        ], 200);
    }
}
