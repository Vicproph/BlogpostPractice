<?php

namespace App\Http\Controllers;

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
        $response = self::makePostRequest([
            'form_params' => [
                'email' => $request->input('email'),
                'password' => $request->input('password')
            ],
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);
        dd(json_decode($response->getBody()));
    }
    public static function makePostRequest($config)
    {
        $client = new Client();
        $response = $client->post('localhost:8001/api/users/login', [
            'form_params' => $config['form_params'],
            'headers' => $config['headers']
        ]);
        return $response;
    }
}
