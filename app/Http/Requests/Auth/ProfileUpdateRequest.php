<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:users,email,' . auth()->user()->id,
            'password' => 'nullable',
            'first_name' => 'nullable',
            'last_name' => 'nullable',
            'phone_no' => 'nullable|unique:users,phone_no,' . auth()->user()->id,
            'image' => 'nullable',
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'Email',
            'password' => 'Password',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'phone_no' => 'Phone Number',
            'image' => 'Image',
        ];
    }
}
