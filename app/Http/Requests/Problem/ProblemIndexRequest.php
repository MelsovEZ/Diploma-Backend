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
            'status' => ['sometimes', 'in:pending,in_progress,in_review,declined,done'],
            'from_date' => ['sometimes', 'date'],
            'to_date' => ['sometimes', 'date', 'after_or_equal:from_date'],
        ];
    }
}
