<?php

namespace App\Http\Requests\Problem;

use Illuminate\Foundation\Http\FormRequest;

class ProblemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'city_id' => 'required|exists:cities,id',
            'district_id' => 'required|exists:districts,id',
            'address' => 'required|string|max:255',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,svg',
        ];
    }
    public function messages(): array
    {
        return [
            'title.required' => 'Название проблемы обязательно.',
            'title.max' => 'Название не должно превышать 255 символов.',
            'description.required' => 'Описание проблемы обязательно.',
            'category_id.required' => 'Категория проблемы обязательна.',
            'category_id.exists' => 'Выбранная категория не существует.',
            'city_id.required' => 'Город обязательен.',
            'city_id.exists' => 'Выбранный город не существует.',
            'district_id.required' => 'Район обязателен.',
            'district_id.exists' => 'Выбранный район не существует.',
            'address.required' => 'Адрес обязательный.',
            'address.max' => 'Адрес не должен превышать 255 символов.',
            'photos.max' => 'Максимальное количество фотографий - 5.',
            'photos.*.image' => 'Каждое фото должно быть изображением.',
            'photos.*.mimes' => 'Каждое фото должно быть формата jpeg, png, jpg, gif или svg.',
            'photos.*.max' => 'Каждое фото не должно превышать 2 MB.',
        ];
    }
}
