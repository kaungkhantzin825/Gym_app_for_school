<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'name' => ['nullable', 'string', 'max:255'],
            'age' => ['nullable', 'integer', 'min:10', 'max:120'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'height' => ['nullable', 'numeric', 'min:50', 'max:300'],
            'current_weight' => ['nullable', 'numeric', 'min:20', 'max:500'],
            'target_weight' => ['nullable', 'numeric', 'min:20', 'max:500'],
            'goal' => ['nullable', 'string', 'in:lose_weight,gain_weight,maintain,build_muscle'],
            'activity_level' => ['nullable', 'string', 'in:sedentary,light,moderate,active,very_active'],
            'daily_calories_target' => ['nullable', 'numeric', 'min:0'],
            'daily_protein_target' => ['nullable', 'numeric', 'min:0'],
            'daily_carbs_target' => ['nullable', 'numeric', 'min:0'],
            'daily_fat_target' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
