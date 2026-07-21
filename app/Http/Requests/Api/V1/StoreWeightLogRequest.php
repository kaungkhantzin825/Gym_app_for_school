<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreWeightLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'weight' => ['required', 'numeric', 'min:20', 'max:500'],
            'notes' => ['nullable', 'string'],
            'logged_at' => ['required', 'date'],
        ];
    }
}
