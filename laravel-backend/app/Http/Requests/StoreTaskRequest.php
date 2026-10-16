<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['required', Rule::in(['LOW', 'MEDIUM', 'HIGH'])],
            'due_date' => ['required', 'date_format:Y-m-d'],
            'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')],
        ];
    }
}
