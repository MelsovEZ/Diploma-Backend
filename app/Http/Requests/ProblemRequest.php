<?php

namespace App\Http\Requests;

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
            'location_lat' => 'required|numeric',
            'location_lng' => 'required|numeric',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }
}
