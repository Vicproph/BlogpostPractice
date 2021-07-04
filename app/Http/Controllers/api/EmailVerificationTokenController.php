<?php

namespace App\Http\Controllers\api;

use App\Events\Registered;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmailVerificationTokenController extends Controller
{
    //
    public function sendVerificationCode($id)
    {
        $userId = $id;
        if (!$this->hasVerifiedAccount($id)){
            $user = User::find($userId);
            event(new Registered($user));
            return response([
                'message' => 'کد تاییدیه به ایمیل شما ارسال شد'
            ]);
        }
        else{
            return response([
                'message' => 'حساب شما قبلا فعال شده است'
            ]);
        }
    }
    public function verifyAccount(Request $request)
    {
        $inputToken = $request->input('token');
        $userId = $request->input('user_id');
        if (!$this->existsInUsers($userId)){
            return response([
               'message' => 'کاربر وجود ندارد'
            ]);
        }
        if($this->hasVerifiedAccount($userId)){
            return response([
               'message' => 'حساب شما در حال حاضر فعال است'
            ]);
        }
        if (!$this->matchesToken($userId,$inputToken)){
            return response([
                'message' => 'کد تاییدیه نا معتبر است'
            ]);
        }

        $this->verifyEmail($userId);
        return response([
            'message' => 'حساب شما با موفقیت فعال شد'
        ]);
    }

    private function existsInUsers($userId)
    {
        $user = User::find($userId);

        return $user!=null;
    }

    private function hasVerifiedAccount($userId)
    {
        $user= User::find($userId);
        return $user->email_verified_at!=null;
    }
    private function matchesToken($userId,$inputToken)
    {

        $user = User::find($userId);
        /**
         * @var $user User
         */
        $tokenInDatabase = $user->emailVerificationToken->token;

        if (Hash::check($inputToken,$tokenInDatabase)){
            return true;
        }
        return false;
    }

    private function verifyEmail($userId)
    {
        /**
         * @var $user User
         */
        $user = User::find($userId);
        $user->emailVerificationToken()->delete();
        $user->email_verified_at = (new \DateTime())->format('Y-m-d h:i:s');
        $user->save();
    }
}
