<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreCameraSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            // Generated on the phone, so a retried upload is idempotent.
            'client_id' => ['nullable', 'string', 'max:64'],
            'exercise_type' => ['required', 'string', 'max:32'],
            'exercise_name' => ['required', 'string', 'max:100'],
            'started_at' => ['required', 'date'],
            'ended_at' => ['required', 'date', 'after_or_equal:started_at'],
            'duration_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'sets_count' => ['nullable', 'integer', 'min:0', 'max:200'],
            'total_reps' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'avg_form_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'sets' => ['nullable', 'array', 'max:200'],
            'sets.*.set_number' => ['nullable', 'integer', 'min:1'],
            'sets.*.reps' => ['nullable', 'integer', 'min:0'],
            'sets.*.form_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'source' => ['nullable', 'string', 'max:32'],
        ];
    }
}
