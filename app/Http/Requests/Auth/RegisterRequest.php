<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|regex:/^\S+$/',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ];
    }
    public function messages(): array
    {
        return [
            'name.regex' => 'Имя не должно содержать пробелы.',
            'email.unique' => 'Эта почта уже занята.',
            'password.confirmed' => 'Пароли не совпадают.',
        ];
    }

}
