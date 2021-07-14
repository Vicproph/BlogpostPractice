<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    //
    public function login()
    {
        return view('auth.login');
    }
    public function signin(Request $request)
    {
        // $response = self::makePostRequest([
        //     'form_params' => [
        //         'email' => $request->input('email'),
        //         'password' => $request->input('password')
        //     ],
        //     'headers' => [
        //         'Accept' => 'application/json'
        //     ]
        // ]);
        // // dd(json_decode($response->getBody()));
        // return json_decode($response->getBody()->getContents());
        $user = User::where("email", $request->input('email'))->first();
        return $user;
    }
    public static function makePostRequest($config)
    {
        $client = new Client();
        $response = $client->post('localhost:8002/api/users/login', [
            'form_params' => $config['form_params'],
            'headers' => $config['headers']
        ]);
        return $response;
    }
}
