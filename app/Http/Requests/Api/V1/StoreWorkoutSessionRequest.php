<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Payload sent once, at the end of a live training session.
 *
 * The mobile app records every set as it happens (reps, weight, RPE, rest and
 * an optional camera-derived form score) and posts the whole session in one
 * request so the workout, its exercises and its sets are written atomically.
 */
class StoreWorkoutSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'in:ai_plan,pose_tracker,manual'],
            'started_at' => ['required', 'date'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'notes' => ['nullable', 'string', 'max:2000'],

            'exercises' => ['required', 'array', 'min:1'],
            'exercises.*.name' => ['required', 'string', 'max:255'],
            'exercises.*.type' => ['required', 'string', 'in:cardio,strength,flexibility,sports'],
            'exercises.*.sets' => ['required', 'array', 'min:1'],

            'exercises.*.sets.*.set_number' => ['required', 'integer', 'min:1'],
            'exercises.*.sets.*.reps' => ['nullable', 'integer', 'min:1'],
            'exercises.*.sets.*.weight' => ['nullable', 'numeric', 'min:0'],
            'exercises.*.sets.*.rpe' => ['nullable', 'integer', 'min:1', 'max:10'],
            'exercises.*.sets.*.rest_time_seconds' => ['nullable', 'integer', 'min:0'],
            'exercises.*.sets.*.form_score' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }
}
