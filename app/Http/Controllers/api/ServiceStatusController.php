<?php

namespace App\Http\Controllers\api;

use App\Events\MadeActivity;
use App\Http\Controllers\Controller;
use App\Mail\NotifyRemainingLoginTimeMail;
use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Testing\Fakes\MailFake;

class ServiceStatusController extends Controller
{
    //
    public function checkDatabaseHealth()
    {
        try {
            $connectionStatus = DB::connection()->getPDO()->getAttribute(\PDO::ATTR_CONNECTION_STATUS);
            return response()->json([
                'healthy' => true,
                'connection_status' => $connectionStatus
            ]);
        } catch (Exception $exception) {
            return response([
                'healthy' => false,
                'cause' => $exception->getMessage()
            ]);
        }
    }

    public function checkMailHealth()
    {
        /**
         * @var $user User
         */
        event(new MadeActivity(Auth::user()));
        $user = Auth::user();
        try {
            Mail::to($user->email)->send(new NotifyRemainingLoginTimeMail());
            return response([
                'healthy' => true
            ]);
        } catch (\Exception $exception) {
            return response([
                'healthy' => false,
                'cause' => $exception->getMessage()
            ]);
        }
    }
    public function checkRedisHealth()
    {

        try {
            Redis::hget('null', 'null'); // just some random command to see if redis responds
            return response([
                'healthy' => true,
            ]);
        } catch (Exception $exception) {
            return response([
                'healthy' => false,
                'cause' => [
                    $exception->getMessage()
                ]
            ]);
        }
    }
}
