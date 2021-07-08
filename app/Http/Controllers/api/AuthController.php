<?php

namespace App\Http\Controllers\api;

use App\Events\LoggedIn;
use App\Events\Registered;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\Image;
use App\Models\Role;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //

    // some ordinary responses

    public function login(Request $request)
    { // اول می فهمیم کاربر با کدام فیلد وارد شده است
        /**
         * @var $user User
         */
        $email = $request->input('email');
        $phoneNumber = $request->input('phone_number');
        $username = $request->input('username');
        $password = $request->input('password');
        if ($this->enteredAtLeastOneId($email, $phoneNumber, $username)) {
            $user = $this->fetchUser($email, $phoneNumber, $username);
            if (!$user) {
                return response([
                    'message' => 'کاربر وجود ندارد'
                ]);
            }

            if (Hash::check($password, $user->password)) {
                if ($user->tokens != null) // اگر توکن قبلی ای وجود داشت آن را پاک می کند
                    $user->tokens()->delete();
                $token = $user->createToken('access_token')->plainTextToken;
                event(new LoggedIn($user));
                return response([
                    'user' => new UserResource($user),
                    'access_token' => $token,
                    'message' => 'مدت زمان وارد بودن در سیستم 10 دقیقه است، پس از آن نیاز به وارد شدن دوباره دارید'
                ]);
            } else {
                return response([
                    'message' => 'اطلاعات ورود نامعتبر است'
                ]);
            }
        } else {
            return response([
                'message' => 'هیچ شناسه ای برای ورود، وارد نکرده اید'
            ]);
        }
    }

    public function loginInsteadOf($id) // Only admins can do this
    {
        if (Auth::user()->can('loginInsteadOf', User::class)) {
            $user = User::findOrFail($id);
            if ($user->isRole(Role::ROLE_USER_TITLE)) {
                $token = $user->createToken('admin_access_token')->plainTextToken;
                return response([
                    'admin_access_token' => $token
                ]);
            } else {
                return response([
                    'errors' => [
                        'message' => 'شما تنها می توانید به عنوان کاربر وارد شوید'
                    ]
                ]);
            }
        } else {
            return response([
                'errors' => [
                    'message' => __('messages.not_authorized')
                ]
            ]);
        };
    }

    public function logout()
    {
        /**
         * @var $user User
         */
        $user = Auth::user();
        $user->tokens()->delete();
        return response([
            'message' => 'شما با موفقیت از سیستم خارج شدید'
        ]);
    }

    public function register(UserRegisterRequest $request)
    {

        $validated = $request->validated();
        $skillsOk = self::hasEnoughSkills($validated['skills']);
        if ($skillsOk) { // فرم به درستی پر شده است
            $user = $this->extractDetails($validated);
            $user->save();
            self::saveAvatar($user, $validated['avatar']);
            self::setSkills($user, $validated['skills']);
            event(new Registered($user));
            $avatar = $user->avatar;
            return response([
                'user' => [
                    'main_data' => $user,
                ],
                'avatar' => $avatar,
                'skills' => $user->skills
            ]);
        } else {
            return response([
                'message' => 'تعداد مهارت ها در محدوده ی مجاز نمی باشد (بین 3 تا 10)'
            ]);
        }
    }

    public static function hasEnoughSkills($skills)
    {
        // این تابع چک می کند که تعداد مهارت ها مجاز است یا خیر
        $skillCount = substr_count($skills, "\n") + 1; // an N line string has N-1 line-breaks
        return $skillCount >= 3 && $skillCount <= 10;
    }

    private function extractDetails($credentials)
    {
        $user = new User;
        $user->firstname = $credentials['firstname'];
        $user->lastname = $credentials['lastname'];
        $user->email = $credentials['email'];
        $user->phone_number = $credentials['phone_number'];
        $user->username = $credentials['username'];
        $user->description = $credentials['description'];
        $user->password = Hash::make($credentials['password']);
        return $user;
    }

    public static function saveAvatar($user, $inputImage) // مسیر آواتار در پایگاه داده را برمی گرداند
    {
        /**
         * @var $user User
         */
        $path = $inputImage->store('avatars');
        $user->avatar()->create(['path' => $path]);
        return $path;
    }

    public static function setSkills($user, $skills)
    {
        /**
         * @var $user User
         */
        $skills = explode("\n", $skills);
        foreach ($skills as $skill) {
            if (!self::existsInSkillsTable($skill)) {
                $user->skills()->create(['skill_title' => $skill]);
            } else {

                if (!$user->skills->contains('skill_title', $skill)) { // ممکن است کاربر در حال آپدیت مهارت ها باشد که در این صورت ممکن است مهارت تکراری که قبلا داشته است را وارد کند و اینجا مانع آن می شویم که دوباره آن مهارت ها مکرارا در پایگاه داده ثبت شوند
                    $roleId = Skill::where('skill_title', $skill)->first();
                    $user->skills()->attach($roleId);
                }
            }
        }
    }

    public static function existsInSkillsTable($skill)
    {
        $skill = Skill::where('skill_title', $skill)->first();
        return $skill != null;
    }

    private function fetchUser($email, $phoneNumber, $username)
    {
        $user = null;
        if ($email) {
            $user = User::where('email', $email)->first();
        } else if ($phoneNumber) {
            $user = User::where('phone_number', $phoneNumber)->first();
        } else if ($username) {
            $user = User::where('username', $username)->first();
        }
        return $user;
    }

    private function enteredAtLeastOneId($email, $phoneNumber, $username)
    {
        return ($email != null) || ($phoneNumber != null) || ($username != null);
    }
}
