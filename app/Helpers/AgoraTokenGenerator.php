<?php

namespace App\Helpers;

use Carbon\Carbon;

class AgoraTokenGenerator
{
    const ROLE_PUBLISHER = 1;
    const ROLE_SUBSCRIBER = 2;

    public static function buildTokenWithUid($appId, $appCertificate, $channelName, $uid, $role, $privilegeExpireTime)
    {
        $currentTimeStamp = Carbon::now()->timestamp;
        $expire = $privilegeExpireTime;
        $uidStr = $uid === 0 ? '' : $uid;

        // Assign privilege
        $privileges = [
            'join_channel' => $expire,
            'publish_audio_stream' => $expire,
            'publish_video_stream' => $expire,
            'publish_data_stream' => $expire
        ];

        $sign = self::generateToken($appId, $appCertificate, $channelName, $uidStr, $privileges);

        return $sign;
    }

    private static function generateToken($appId, $appCertificate, $channelName, $uidStr, $privileges)
    {
        $msg = self::generateMsg($appId, $channelName, $uidStr, $privileges);
        $sig = self::hmacSign($msg, $appCertificate);
        return $msg . $sig;
    }

    private static function generateMsg($appId, $channelName, $uidStr, $privileges)
    {
        $msg = '';
        $msg .= self::packString($appId);
        $msg .= self::packString($channelName);
        $msg .= self::packString($uidStr);
        $msg .= pack('v', count($privileges));
        foreach ($privileges as $key => $value) {
            $msg .= self::packString($key);
            $msg .= pack('V', $value);
        }
        return $msg;
    }

    private static function packString($value)
    {
        return pack('v', strlen($value)) . $value;
    }

    private static function hmacSign($msg, $appCertificate)
    {
        return hash_hmac('sha1', $msg, $appCertificate, true);
    }
}
