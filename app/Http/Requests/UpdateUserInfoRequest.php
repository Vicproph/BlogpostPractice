<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserInfoRequest extends FormRequest
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
            'firstname' => ['nullable',],
            'lastname' => ['nullable',],
            'avatar' => ['nullable', 'image', 'max:2000'],
            'username' => ['nullable', 'unique:users'],
            'description' => ['nullable', 'min:80'],
            'skills' => ['nullable'],
            'password' => ['nullable'],
        ];
    }
}
