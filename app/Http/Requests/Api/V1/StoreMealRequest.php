<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreMealRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            // Either the AI scan's stored path or an uploaded file; never a
            // free-form string, so a client cannot point a meal at anything.
            'photo_path' => ['nullable', 'string', 'regex:#^(meal-scans|meals)/[A-Za-z0-9._-]+$#'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'meal_time' => ['required', 'date'],
        ];
    }
}
