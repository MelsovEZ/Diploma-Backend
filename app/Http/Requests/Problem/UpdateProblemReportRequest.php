<?php
namespace App\Http\Requests\Problem;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProblemReportRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => 'nullable|string',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }
}
