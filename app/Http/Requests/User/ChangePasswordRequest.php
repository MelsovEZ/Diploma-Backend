<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'old_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'old_password.required' => 'Введите текущий пароль',
            'new_password.required' => 'Введите новый пароль',
            'new_password.min' => 'Новый пароль должен быть не менее 6 символов',
            'new_password.confirmed' => 'Пароли не совпадают',
        ];
    }
}
