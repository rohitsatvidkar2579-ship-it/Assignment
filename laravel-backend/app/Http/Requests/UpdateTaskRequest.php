<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['sometimes', 'required', Rule::in(['LOW', 'MEDIUM', 'HIGH'])],
            'due_date' => ['sometimes', 'required', 'date_format:Y-m-d'],
            'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')],
        ];
    }
}
