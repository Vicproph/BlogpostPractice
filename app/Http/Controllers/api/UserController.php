<?php

namespace App\Http\Controllers\api;

use App\Events\MadeActivity;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserInfoRequest;
use App\Http\Resources\PostCollection;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
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
        event(new MadeActivity($user));
        return $user->unreadNotifications;
    }
    public function getLastActivityTime($userId)
    {
        event(new MadeActivity(Auth::user()));
        $user = User::find($userId);
        $lastActivityTime = self::calculateLastActivity($user);
        return $lastActivityTime;
    }
    public function update(UpdateUserInfoRequest $request)
    {
        event(new MadeActivity(Auth::user()));
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
    public static function calculateLastActivity($user)
    {

        $timestamp = $user->last_activity_at;
        if ($timestamp == null) {
            return response([
                'status' => 'never has been online'
            ]);
        }
        $seconds = self::findDifferenceInSeconds($user->last_activity_at);
        echo "$seconds\n";
        $minutesAgo = floor($seconds / 60);
        if ($minutesAgo <= 10) {
            return response([
                'status' => 'Online',
            ]);
        } else {
            if ($minutesAgo < 60)
                return response([
                    'status' => "Online $minutesAgo minutes ago"
                ]);
            else {
                $minutesAgo = $minutesAgo % 60;
                $hoursAgo = floor($minutesAgo / 60);
                if ($hoursAgo < 24)
                    return response([
                        'status' => "Online $hoursAgo hours and $minutesAgo minutes ago"
                    ]);
                else {
                    $hoursAgo = $hoursAgo % 24;
                    $daysAgo = $hoursAgo / 24;
                    return response([
                        'status' => "Online $daysAgo Days and $hoursAge hours and $minutesAgo minutes ago"
                    ]);
                }
            }
        }
    }
    public static function findDifferenceInSeconds($dateTime)
    {
        $timeFirst  = strtotime($dateTime);
        $timeSecond = strtotime(time());
        $differenceInSeconds = $timeSecond - $timeFirst;
        return $differenceInSeconds;
    }

    public static function updateAvatarImage(User $user, $file)
    {
        Storage::delete($user->avatar->path);
        $user->avatar()->delete();
        AuthController::saveAvatar($user, $file);
    }
}
