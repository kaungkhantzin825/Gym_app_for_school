<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class GenerateWorkoutRequest extends FormRequest
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
            'fitness_level' => ['required', 'string', 'max:100'],
            'primary_goal' => ['required', 'string', 'max:100'],
            'duration_minutes' => ['required', 'integer', 'min:10', 'max:180'],
            'available_equipment' => ['required', 'string', 'max:500'],
            'focus_area' => ['required', 'string', 'max:255'],
        ];
    }
}
