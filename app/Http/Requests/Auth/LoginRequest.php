<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|string|email',
            'password' => 'required|string|min:8',
        ];
    }
    public function messages(): array
    {
        return [
            'email.required' => 'Почта обязательна.',
            'email.string' => 'Неверный логин или пароль.',
            'email.email' => 'Неверный формат почты.',
            'email.regex' => 'Неверный формат почты.',
            'password.required' => 'Пароль обязателен.',
            'password.string' => 'Неверный логин или пароль.',
            'password.min' => 'Неверный логин или пароль.',
        ];
    }
}

