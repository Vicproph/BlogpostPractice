<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserInfoRequest;
use App\Http\Resources\PostCollection;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    //
    public function index()
    {
        $user = Auth::user();

        if ($user->can('viewAny', User::class)) {
            $users = User::all();
            return response([
                new UserCollection($users)
            ]);
        } else {
            return response('messages.not_authorized');
        }
    }
    public function getUnreadNotifications()
    {
        /**
         * @var $user User
         */
        $user = Auth::user();
        return $user->unreadNotifications;
    }
    public function update(UpdateUserInfoRequest $request)
    {
        $validated = $request->validated();
        $skillsOk = AuthController::hasEnoughSkills($validated['skills']);
        if ($skillsOk) {
            if (self::hasEnteredAtLeastOneField($validated)) {
                if ($this->hasNotEnteredIllegalFields($validated)) {
                    $user = $this->updateUserFromInputs($validated);
                    $user->save();
                    return response([
                        'message' => 'اطلاعات شما با موفقیت به روزرسانی شد'
                    ]);
                } else {
                    return response([
                        'message' => 'شما نمی توانید ایمیل یا شماره تلفن خود را تغییر دهید'
                    ]);
                }
            }
        } else {
            return response([
                'message' => 'تعداد مهارت ها باید بین 3 تا 10 باشد'
            ]);
        }
    }
    public static function hasEnteredAtLeastOneField($inputs): bool
    {
        $atLeastOneExists = false;
        foreach ($inputs as $input)
            $atLeastOneExists = $input || $atLeastOneExists;
        return $atLeastOneExists;
    }
    private function hasNotEnteredIllegalFields($inputs): bool
    {
        // این تابع چک می کند که آیا ایمیل و شماره تلفن وارد شده اند یا خیر (که نباید وارد شوند)
        return (isset($inputs['email']) ? false : true) && (isset($inputs['phone_number']) ? false : true);
    }

    private function updateUserFromInputs($inputs): User
    {
        /**
         * @var $user User
         */
        $user = User::find($inputs['id']);
        $username = $inputs['username'] ?? null;
        $password = $inputs['password'] ?? null;
        $avatar = $inputs['avatar'] ?? null;
        $description = $inputs['description'] ?? null;
        $firstname = $inputs['firstname'] ?? null;
        $lastname = $inputs['lastname'] ?? null;
        $skills = $inputs['skills'] ?? null;
        // در دسته شرط های زیر اگه فیلد ها خالی نبودند باید آپدیت شوند
        if ($username != null) {
            $user->username = $username;
        }
        if ($password != null) {
            $user->password = Hash::make($password);
        }
        if ($avatar != null) {
            self::updateAvatarImage($user, $avatar);
        }
        if ($description != null) {
            $user->description = $description;
        }
        if ($firstname != null) {
            $user->firstname = $firstname;
        }
        if ($lastname != null) {
            $user->lastname = $lastname;
        }
        if ($skills != null) {
            AuthController::setSkills($user, $skills);
        }
        return $user;
    }
    public static function updateAvatarImage(User $user, $file)
    {
        Storage::delete($user->avatar->path);
        $user->avatar()->delete();
        AuthController::saveAvatar($user, $file);
    }
}
