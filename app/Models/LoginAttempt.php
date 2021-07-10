<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    use HasFactory;

    const MAX_LOGIN_ATTEMPT = 3;

    public static function incrementLoginAttempts($ip)
    {
        $loginAttempt = self::where('ip', $ip)->first();
        if ($loginAttempt == null) {
            $loginAttempt = new LoginAttempt;
            $loginAttempt->ip = $ip;
        }
        if ($loginAttempt->attempts < 127) //tiny int overflows if it gets more
            $loginAttempt->attempts++;
        $loginAttempt->save();
    }

    public static function deleteLoginAttempts($ip)
    {
        $loginAttempt = self::where('ip', $ip)->first();
        if ($loginAttempt != null)
            $loginAttempt->delete();
    }

    public static function getLoginAttempts($ip)
    {
        $loginAttempt = self::where('ip', $ip)->first();
        if ($loginAttempt != null)
            return $loginAttempt->attempts;
        return 0;
    }

    public static function reachedMaximumAllowedLoginAttempt($ip)
    {
        $loginAttempt = self::where('ip', $ip)->first();
        if ($loginAttempt != null)
            return $loginAttempt->attempts >= self::MAX_LOGIN_ATTEMPT;
        return false;
    }
}
