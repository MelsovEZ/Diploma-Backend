<?php

namespace App\Http\Requests\Problem;

use Illuminate\Foundation\Http\FormRequest;

class ProblemIndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'array'],
            'category_id.*' => ['integer'],
            'sort' => ['sometimes', 'in:asc,desc'],
            'status' => ['sometimes', 'in:pending,in_progress,declined,done'],
        ];
    }
}
