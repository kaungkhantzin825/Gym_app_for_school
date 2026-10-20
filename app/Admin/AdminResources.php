<?php

namespace App\Admin;

use App\Models\AppSetting;
use App\Models\Exercise;
use App\Models\Meal;
use App\Models\SupportMessage;
use App\Models\TutorialVideo;
use App\Models\User;
use App\Models\Workout;

/**
 * One definition per admin screen.
 *
 * The panel used to be seven hand-written Filament resources. They all did the
 * same three things — list, create, edit — so the screens are now driven from
 * this table instead: the controller reads it, the Vue table and form render
 * from it, and adding a screen means adding one entry here.
 *
 * Column types: text, number, date, datetime, badge, relation, long.
 * Field types:  text, email, number, textarea, select, datetime, password.
 */
class AdminResources
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            'users' => [
                'label' => 'Members',
                'singular' => 'Member',
                'model' => User::class,
                'group' => 'Members',
                'icon' => 'users',
                'search' => ['name', 'email', 'phone'],
                'sort' => ['created_at', 'desc'],
                'with' => [],
                'columns' => [
                    ['key' => 'name', 'label' => 'Name', 'type' => 'text'],
                    ['key' => 'email', 'label' => 'Email', 'type' => 'text'],
                    ['key' => 'phone', 'label' => 'Phone', 'type' => 'text'],
                    ['key' => 'role', 'label' => 'Role', 'type' => 'badge', 'tones' => ['admin' => 'lime', 'user' => 'slate']],
                    ['key' => 'created_at', 'label' => 'Joined', 'type' => 'date'],
                ],
                'fields' => [
                    ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                    ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => ['required', 'email', 'max:255', 'unique:users,email,{id}']],
                    ['key' => 'phone', 'label' => 'Phone', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:32']],
                    ['key' => 'role', 'label' => 'Role', 'type' => 'select', 'options' => ['user' => 'Member', 'admin' => 'Admin'], 'rules' => ['required', 'in:user,admin']],
                    ['key' => 'password', 'label' => 'Password', 'type' => 'password', 'createOnly' => true, 'rules' => ['nullable', 'string', 'min:8']],
                ],
            ],

            'exercises' => [
                'label' => 'Exercises',
                'singular' => 'Exercise',
                'model' => Exercise::class,
                'group' => 'Training',
                'icon' => 'dumbbell',
                'search' => ['exercise_name'],
                'sort' => ['exercise_time', 'desc'],
                'with' => ['user:id,name'],
                'owner' => 'user_id',
                'timeField' => 'exercise_time',
                'columns' => [
                    ['key' => 'exercise_name', 'label' => 'Exercise', 'type' => 'text'],
                    ['key' => 'exercise_type', 'label' => 'Type', 'type' => 'badge', 'tones' => ['strength' => 'lime', 'cardio' => 'sky', 'flexibility' => 'violet', 'sports' => 'amber']],
                    ['key' => 'user.name', 'label' => 'Owner', 'type' => 'relation'],
                    ['key' => 'sets', 'label' => 'Sets', 'type' => 'number'],
                    ['key' => 'reps', 'label' => 'Reps', 'type' => 'number'],
                    ['key' => 'weight', 'label' => 'Weight', 'type' => 'number', 'suffix' => ' kg'],
                    ['key' => 'exercise_time', 'label' => 'Performed', 'type' => 'datetime'],
                ],
                'fields' => [
                    ['key' => 'exercise_name', 'label' => 'Name', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                    ['key' => 'exercise_type', 'label' => 'Type', 'type' => 'select', 'options' => ['strength' => 'Strength', 'cardio' => 'Cardio', 'flexibility' => 'Flexibility', 'sports' => 'Sports'], 'rules' => ['required', 'in:strength,cardio,flexibility,sports']],
                    ['key' => 'duration_minutes', 'label' => 'Duration', 'type' => 'number', 'suffix' => 'min', 'rules' => ['nullable', 'integer', 'min:0', 'max:1440']],
                    ['key' => 'calories_burned', 'label' => 'Calories', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']],
                    ['key' => 'sets', 'label' => 'Sets', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
                    ['key' => 'reps', 'label' => 'Reps', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
                    ['key' => 'weight', 'label' => 'Weight', 'type' => 'number', 'step' => '0.01', 'suffix' => 'kg', 'rules' => ['nullable', 'numeric', 'min:0']],
                    ['key' => 'exercise_time', 'label' => 'Performed at', 'type' => 'datetime', 'rules' => ['required', 'date']],
                ],
            ],

            'workouts' => [
                'label' => 'Workouts',
                'singular' => 'Workout',
                'model' => Workout::class,
                'group' => 'Training',
                'icon' => 'activity',
                'search' => ['name'],
                'sort' => ['started_at', 'desc'],
                'with' => ['user:id,name'],
                'owner' => 'user_id',
                'timeField' => 'started_at',
                'columns' => [
                    ['key' => 'name', 'label' => 'Session', 'type' => 'text'],
                    ['key' => 'user.name', 'label' => 'Member', 'type' => 'relation'],
                    ['key' => 'started_at', 'label' => 'Started', 'type' => 'datetime'],
                    ['key' => 'total_volume', 'label' => 'Volume', 'type' => 'number', 'suffix' => ' kg'],
                    ['key' => 'total_reps', 'label' => 'Reps', 'type' => 'number'],
                    ['key' => 'total_duration_minutes', 'label' => 'Duration', 'type' => 'number', 'suffix' => ' min'],
                ],
                'fields' => [
                    ['key' => 'name', 'label' => 'Session name', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                    ['key' => 'started_at', 'label' => 'Started at', 'type' => 'datetime', 'rules' => ['nullable', 'date']],
                    ['key' => 'ended_at', 'label' => 'Ended at', 'type' => 'datetime', 'rules' => ['nullable', 'date', 'after_or_equal:started_at']],
                    ['key' => 'total_volume', 'label' => 'Total volume', 'type' => 'number', 'step' => '0.01', 'suffix' => 'kg', 'rules' => ['nullable', 'numeric', 'min:0']],
                    ['key' => 'total_reps', 'label' => 'Total reps', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
                    ['key' => 'total_duration_minutes', 'label' => 'Duration', 'type' => 'number', 'suffix' => 'min', 'rules' => ['nullable', 'integer', 'min:0']],
                    ['key' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:2000']],
                ],
            ],

            'meals' => [
                'label' => 'Meals',
                'singular' => 'Meal',
                'model' => Meal::class,
                'group' => 'Nutrition',
                'icon' => 'utensils',
                'search' => ['name', 'notes'],
                'sort' => ['meal_time', 'desc'],
                'with' => ['user:id,name'],
                'owner' => 'user_id',
                'timeField' => 'meal_time',
                'columns' => [
                    ['key' => 'name', 'label' => 'Meal', 'type' => 'text'],
                    ['key' => 'user.name', 'label' => 'Member', 'type' => 'relation'],
                    ['key' => 'total_calories', 'label' => 'kcal', 'type' => 'number'],
                    ['key' => 'total_protein', 'label' => 'Protein', 'type' => 'number', 'suffix' => ' g'],
                    ['key' => 'total_carbs', 'label' => 'Carbs', 'type' => 'number', 'suffix' => ' g'],
                    ['key' => 'meal_time', 'label' => 'Eaten', 'type' => 'datetime'],
                ],
                'fields' => [
                    ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                    ['key' => 'meal_time', 'label' => 'Eaten at', 'type' => 'datetime', 'rules' => ['required', 'date']],
                    ['key' => 'total_calories', 'label' => 'Calories', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']],
                    ['key' => 'total_protein', 'label' => 'Protein', 'type' => 'number', 'step' => '0.01', 'suffix' => 'g', 'rules' => ['nullable', 'numeric', 'min:0']],
                    ['key' => 'total_carbs', 'label' => 'Carbs', 'type' => 'number', 'step' => '0.01', 'suffix' => 'g', 'rules' => ['nullable', 'numeric', 'min:0']],
                    ['key' => 'total_fat', 'label' => 'Fat', 'type' => 'number', 'step' => '0.01', 'suffix' => 'g', 'rules' => ['nullable', 'numeric', 'min:0']],
                    ['key' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:2000']],
                ],
            ],

            'tutorial-videos' => [
                'label' => 'Tutorials',
                'singular' => 'Tutorial',
                'model' => TutorialVideo::class,
                'group' => 'Content',
                'icon' => 'play',
                'search' => ['title', 'muscle_group'],
                'sort' => ['created_at', 'desc'],
                'with' => [],
                'columns' => [
                    ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
                    ['key' => 'muscle_group', 'label' => 'Muscle group', 'type' => 'badge', 'tones' => []],
                    ['key' => 'gender_target', 'label' => 'Audience', 'type' => 'badge', 'tones' => ['both' => 'slate', 'boy' => 'sky', 'girl' => 'violet']],
                    ['key' => 'created_at', 'label' => 'Added', 'type' => 'date'],
                ],
                'fields' => [
                    ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                    ['key' => 'muscle_group', 'label' => 'Muscle group', 'type' => 'select', 'options' => ['chest' => 'Chest', 'back' => 'Back', 'shoulders' => 'Shoulders', 'arms' => 'Arms', 'legs' => 'Legs', 'core' => 'Core', 'full_body' => 'Full body'], 'rules' => ['required', 'string', 'max:64']],
                    ['key' => 'gender_target', 'label' => 'Audience', 'type' => 'select', 'options' => ['both' => 'Everyone', 'boy' => 'Men', 'girl' => 'Women'], 'rules' => ['required', 'in:both,boy,girl']],
                    ['key' => 'video_url', 'label' => 'Video URL', 'type' => 'text', 'rules' => ['required', 'string', 'max:2048']],
                    ['key' => 'thumbnail_url', 'label' => 'Thumbnail URL', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:2048']],
                    ['key' => 'description', 'label' => 'Description', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:2000']],
                ],
            ],

            'support-messages' => [
                'label' => 'Support',
                'singular' => 'Message',
                'model' => SupportMessage::class,
                'group' => 'Support',
                'icon' => 'inbox',
                'search' => ['subject', 'message'],
                'sort' => ['created_at', 'desc'],
                'with' => ['user:id,name'],
                'columns' => [
                    ['key' => 'subject', 'label' => 'Subject', 'type' => 'text'],
                    ['key' => 'user.name', 'label' => 'Member', 'type' => 'relation'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge', 'tones' => ['pending' => 'amber', 'replied' => 'lime', 'closed' => 'slate']],
                    ['key' => 'created_at', 'label' => 'Received', 'type' => 'datetime'],
                ],
                'fields' => [
                    ['key' => 'subject', 'label' => 'Subject', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                    ['key' => 'message', 'label' => 'Message', 'type' => 'textarea', 'rules' => ['required', 'string', 'max:5000']],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['pending' => 'Pending', 'replied' => 'Replied', 'closed' => 'Closed'], 'rules' => ['required', 'in:pending,replied,closed']],
                    ['key' => 'admin_reply', 'label' => 'Reply', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:5000']],
                ],
            ],

            'app-settings' => [
                'label' => 'Settings',
                'singular' => 'Setting',
                'model' => AppSetting::class,
                'group' => 'System',
                'icon' => 'settings',
                'search' => ['key'],
                'sort' => ['key', 'asc'],
                'with' => [],
                'columns' => [
                    ['key' => 'key', 'label' => 'Key', 'type' => 'text'],
                    ['key' => 'value', 'label' => 'Value', 'type' => 'long'],
                    ['key' => 'updated_at', 'label' => 'Updated', 'type' => 'datetime'],
                ],
                'fields' => [
                    ['key' => 'key', 'label' => 'Key', 'type' => 'text', 'rules' => ['required', 'string', 'max:191', 'unique:app_settings,key,{id}']],
                    ['key' => 'value', 'label' => 'Value', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:5000']],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function get(string $key): array
    {
        $resource = static::all()[$key] ?? null;

        abort_if($resource === null, 404);

        return $resource;
    }

    public static function exists(string $key): bool
    {
        return array_key_exists($key, static::all());
    }

    /**
     * Sidebar groups in display order, each with its resources.
     *
     * @return array<int, array{group: string, items: array<int, array{key: string, label: string, icon: string}>}>
     */
    public static function navigation(): array
    {
        $order = ['Training', 'Nutrition', 'Content', 'Members', 'Support', 'System'];
        $grouped = [];

        foreach (static::all() as $key => $resource) {
            $grouped[$resource['group']][] = [
                'key' => $key,
                'label' => $resource['label'],
                'icon' => $resource['icon'],
            ];
        }

        $navigation = [];

        foreach ($order as $group) {
            if (isset($grouped[$group])) {
                $navigation[] = ['group' => $group, 'items' => $grouped[$group]];
            }
        }

        return $navigation;
    }
}
