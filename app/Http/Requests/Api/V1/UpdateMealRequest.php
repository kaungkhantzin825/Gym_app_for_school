<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMealRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'in:breakfast,lunch,dinner,snack'],
            'photo_path' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'meal_time' => ['sometimes', 'date'],
        ];
    }
}
