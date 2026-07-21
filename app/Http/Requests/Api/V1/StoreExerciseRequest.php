<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreExerciseRequest extends FormRequest
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
            'exercise_name' => ['required', 'string', 'max:255'],
            'exercise_type' => ['required', 'string', 'in:cardio,strength,flexibility,sports'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'calories_burned' => ['nullable', 'numeric', 'min:0'],
            'sets' => ['nullable', 'integer', 'min:1'],
            'reps' => ['nullable', 'integer', 'min:1'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'exercise_time' => ['required', 'date'],
        ];
    }
}
