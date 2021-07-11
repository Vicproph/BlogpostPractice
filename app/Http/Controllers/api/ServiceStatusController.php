<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Mail\NotifyRemainingLoginTimeMail;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Testing\Fakes\MailFake;

class ServiceStatusController extends Controller
{
    //
    public function checkDatabaseHealth()
    {
        try {
            $connectionStatus = DB::connection()->getPDO()->getAttribute(\PDO::ATTR_CONNECTION_STATUS);
            return response()->json([
                'healthy'=> true,
                'connection_status' =>$connectionStatus
            ]);
        }catch (\PDOException $exception){
            return response([
                'healthy'=> false,
                'cause' =>$exception->getMessage()
            ]);
        }
    }

    public function checkMailHealth()
    {
        /**
         * @var $user User
         */
        $user = Auth::user();
        try {
            Mail::to($user->email)->send(new NotifyRemainingLoginTimeMail());
            return response([
               'healthy' => true
            ]);
        }catch (\Exception $exception){
            return response([
                'healthy'=>false,
                'cause'=> $exception->getMessage()
            ]);
        }
    }
}
