<?php
namespace App\Http\Requests\Problem;

use Illuminate\Foundation\Http\FormRequest;

class SubmitProblemReportRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => 'nullable|string',
            'photos' => 'required|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }
}
