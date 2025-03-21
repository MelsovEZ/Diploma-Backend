<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'problem_id' => 'required|integer|exists:problems,problem_id',
            'text' => 'required|string|max:1000',
        ];
    }
}
