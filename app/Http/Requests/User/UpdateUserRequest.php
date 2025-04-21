<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|nullable|string|max:255|regex:/^[^\s]+$/',
            'surname' => 'sometimes|nullable|string|max:255|regex:/^[^\s]+$/',
            'photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
    /**
     * Get custom error messages for validator.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.sometimes' => 'Имя обязательно при изменении.',
            'name.string' => 'Имя должно быть строкой.',
            'name.max' => 'Имя не может быть длиннее 255 символов.',
            'name.regex' => 'Имя не должно содержать пробелы или быть пустым.',
            'surname.string' => 'Фамилия должна быть строкой.',
            'surname.max' => 'Фамилия не может быть длиннее 255 символов.',
            'surname.regex' => 'Фамилия не должна содержать пробелы или быть пустой.',
            'photo.sometimes' => 'Фото обязательна при изменении.',
            'photo.image' => 'Фото должно быть изображением.',
            'photo.mimes' => 'Фото должно быть формата jpeg, png, jpg или gif.',
            'photo.max' => 'Размер фото не может превышать 2 МБ.',
        ];
    }
}
