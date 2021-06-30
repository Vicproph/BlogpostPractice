<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
            'firstname' =>['required',],
            'lastname'=>['required',],
            'email'=>['required','unique:users'],
            'phone_number'=>['unique:users','nullable'],
            'avatar'=>['required','image','max:2000'],
            'username'=>['required','unique:users'],
            'description'=>['required','min:80'],
            'skills'=>['required'],
            'password' =>['required']
        ];
    }
}
