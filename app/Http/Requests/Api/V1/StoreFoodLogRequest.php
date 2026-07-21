<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreFoodLogRequest extends FormRequest
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
            'food_name' => ['required', 'string', 'max:255'],
            'fatsecret_food_id' => ['nullable', 'string'],
            'brand_name' => ['nullable', 'string', 'max:255'],
            'serving_size' => ['nullable', 'numeric', 'min:0'],
            'serving_unit' => ['nullable', 'string', 'max:50'],
            'calories' => ['required', 'numeric', 'min:0'],
            'protein' => ['required', 'numeric', 'min:0'],
            'carbs' => ['required', 'numeric', 'min:0'],
            'fat' => ['required', 'numeric', 'min:0'],
            'fiber' => ['nullable', 'numeric', 'min:0'],
            'sugar' => ['nullable', 'numeric', 'min:0'],
            'sodium' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
