<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'regex:/^(\+40|0)[0-9]{9}$/'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'user_type' => ['required', 'in:student,tutor'],
            'location_id' => ['required_if:user_type,tutor', 'exists:locations,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Please enter a valid Romanian phone number.',
            'location_id.required_if' => 'Location is required for tutors.',
        ];
    }
}
