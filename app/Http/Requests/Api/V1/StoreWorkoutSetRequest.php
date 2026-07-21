<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkoutSetRequest extends FormRequest
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
            'exercise_id' => ['required', 'exists:exercises,id'],
            'set_number' => ['required', 'integer', 'min:1'],
            'reps' => ['nullable', 'integer', 'min:1'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'rpe' => ['nullable', 'integer', 'min:1', 'max:10'],
            'rest_time_seconds' => ['nullable', 'integer', 'min:0'],
            'is_superset' => ['nullable', 'boolean'],
        ];
    }
}
