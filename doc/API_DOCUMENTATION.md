# GYM APP - API Documentation

## Base URL
```
http://localhost:8000/api/v1
```

## Uploaded media

Avatars and progress photos are **streamed by PHP**, not served from `/storage`:

```
GET /api/v1/media/{directory}/{filename}      # public
```

`directory` is one of `profile-photos` or `gallery`. Every `profile_photo_url`
in a response already points here, so clients never need to build the URL
themselves.

> `/storage/...` depends on `php artisan storage:link` and on the web server
> being allowed to follow that symlink. On a host where either is missing the
> whole prefix answers `403`, which is why photo URLs are not built from
> `Storage::disk('public')->url()` any more.

---

## Authentication
All authenticated endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {token}
```

Tokens are obtained via the `/register` or `/login` endpoints using Laravel Sanctum.

---

## Endpoints

### 1. Authentication

#### POST /register
Create a new user account.

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response (201):**
```json
{
    "message": "Registration successful",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "+1234567890",
        "role": "user",
        "created_at": "2026-05-05T08:00:00.000000Z"
    },
    "token": "1|abc123..."
}
```

---

#### POST /login
Authenticate an existing user.

**Request Body:**
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response (200):**
```json
{
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "token": "2|xyz789..."
}
```

**Error (422):**
```json
{
    "message": "The provided credentials are incorrect.",
    "errors": {
        "email": ["The provided credentials are incorrect."]
    }
}
```

---

#### POST /logout
🔒 **Requires Authentication**

Revoke the current access token.

**Response (200):**
```json
{
    "message": "Logged out successfully"
}
```

---

#### GET /user
🔒 **Requires Authentication**

Get the authenticated user with profile.

**Response (200):**
```json
{
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "role": "user",
    "profile": {
        "age": 28,
        "gender": "male",
        "height": "175.00",
        "current_weight": "80.00",
        "target_weight": "75.00",
        "goal": "lose_weight",
        "activity_level": "moderate",
        "daily_calories_target": "2200.00",
        "daily_protein_target": "150.00",
        "daily_carbs_target": "250.00",
        "daily_fat_target": "70.00"
    }
}
```

---

#### PUT /password
🔒 **Requires Authentication**

Change the authenticated user's password.

**Request Body:**
```json
{
    "current_password": "old-password",
    "password": "new-password",
    "password_confirmation": "new-password"
}
```

**Field Constraints:**
- `current_password`: must match the authenticated user's current password
- `password`: minimum 8 characters, must be different from `current_password`, must match `password_confirmation`

**Response (200):**
```json
{
    "message": "Password changed successfully"
}
```

**Error (422):**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "current_password": ["The current password is incorrect."]
    }
}
```

---

### 2. User Profile

#### GET /profile
🔒 **Requires Authentication**

Get the user's fitness profile.

**Response (200):**
```json
{
    "name": "John Doe",
    "profile_photo": "profile-photos/example.jpg",
    "profile_photo_url": "http://localhost:8000/storage/profile-photos/example.jpg",
    "id": 1,
    "user_id": 1,
    "age": 28,
    "gender": "male",
    "height": "175.00",
    "current_weight": "80.00",
    "target_weight": "75.00",
    "goal": "lose_weight",
    "activity_level": "moderate",
    "daily_calories_target": "2200.00",
    "daily_protein_target": "150.00",
    "daily_carbs_target": "250.00",
    "daily_fat_target": "70.00"
}
```

---

#### PUT /profile
🔒 **Requires Authentication**

Update the user's fitness profile (creates if doesn't exist).

**Request Body:**
```json
{
    "name": "John Doe",
    "age": 28,
    "gender": "male",
    "height": 175.00,
    "current_weight": 80.00,
    "target_weight": 75.00,
    "goal": "lose_weight",
    "activity_level": "moderate",
    "daily_calories_target": 2200,
    "daily_protein_target": 150,
    "daily_carbs_target": 250,
    "daily_fat_target": 70
}
```

**Field Constraints:**
- `name`: optional, maximum 255 characters
- `gender`: `male`, `female`, `other`
- `goal`: `lose_weight`, `gain_weight`, `maintain`, `build_muscle`
- `activity_level`: `sedentary`, `light`, `moderate`, `active`, `very_active`

**Response (200):** Returns the updated profile object including `name`, `profile_photo`, and `profile_photo_url`.

---

#### POST /profile/photo
🔒 **Requires Authentication**

Upload or replace the authenticated user's profile photo.

**Request Body (multipart/form-data):**
- `photo` (file, required): Image file (max 5MB)

**Response (201):**
```json
{
    "message": "Profile photo uploaded successfully",
    "profile_photo": "profile-photos/abc123.jpg",
    "profile_photo_url": "http://localhost:8000/storage/profile-photos/abc123.jpg"
}
```

---

### 3. Meals

#### GET /meals
🔒 **Requires Authentication**

List user's meals. Supports filtering by date.

**Query Parameters:**
- `date` (optional): Filter by date (YYYY-MM-DD)
- `page` (optional): Page number for pagination

**Response (200):** Paginated list of meals with food logs.

---

#### POST /meals
🔒 **Requires Authentication**

Create a new meal.

**Request Body:**
```json
{
    "name": "breakfast",
    "notes": "Morning meal",
    "meal_time": "2026-05-05 08:00:00",
    "photo_path": "meal-scans/abc123.jpg"
}
```

**Field Constraints:**
- `name`: free-form, up to 255 characters (the app saves the AI's own meal
  name, e.g. "Southeast Asian rice meal")
- `photo_path`: optional. Either `meal-scans/<file>` (the path the AI analyser
  already stored) or `meals/<file>` (an upload). Anything else is rejected with
  422 - a meal may only point at a file the API can serve.

**Response (201):** The created meal object, including a computed `photo_url`.

---

#### GET /meals/{id}
🔒 **Requires Authentication**

Get a specific meal with food logs.

**Response (200):**
```json
{
    "id": 1,
    "user_id": 1,
    "name": "breakfast",
    "photo_path": "meal-scans/abc123.jpg",
    "photo_url": "https://gymapp.mmcertify.com/api/v1/media/meal-scans/abc123.jpg",
    "notes": "Morning meal",
    "total_calories": "450.00",
    "total_protein": "30.00",
    "total_carbs": "50.00",
    "total_fat": "15.00",
    "meal_time": "2026-05-05T08:00:00.000000Z",
    "food_logs": [
        {
            "id": 1,
            "food_name": "Oatmeal",
            "calories": "150.00",
            "protein": "5.00",
            "carbs": "27.00",
            "fat": "3.00"
        }
    ]
}
```

`photo_url` is computed, not stored. It always points at
`/api/v1/media/...`, never `/storage/...`, because the storage symlink is not
followed on the production host. `photo_url` is `null` when the meal has no
photo.

---

#### POST /meals/{id}/photo
🔒 **Requires Authentication**

Attach or replace a meal's photo. Multipart form-data with an `image` field
(jpg/png/webp, max 10 MB). Replacing deletes the previous file.

**Response (200):** The updated meal, including `photo_url`.

---

#### DELETE /meals/{id}/photo
🔒 **Requires Authentication**

Remove a meal's photo and delete the file.

**Response (200):** The updated meal with `photo_path` and `photo_url` set to
`null`.

---

#### PUT /meals/{id}
🔒 **Requires Authentication**

Update a meal.

---

#### DELETE /meals/{id}
🔒 **Requires Authentication**

Soft-delete a meal.

**Response (200):**
```json
{
    "message": "Meal deleted successfully"
}
```

---

### 4. Food Logs

#### POST /meals/{meal_id}/food-logs
🔒 **Requires Authentication**

Add a food item to a meal. Automatically recalculates meal totals.

**Request Body:**
```json
{
    "food_name": "Chicken Breast",
    "fatsecret_food_id": "123456",
    "brand_name": null,
    "serving_size": 150,
    "serving_unit": "g",
    "calories": 248,
    "protein": 46.5,
    "carbs": 0,
    "fat": 5.4,
    "fiber": 0,
    "sugar": 0,
    "sodium": 120
}
```

**Response (201):** The created food log object.

---

#### DELETE /meals/{meal_id}/food-logs/{food_log_id}
🔒 **Requires Authentication**

Remove a food item from a meal. Automatically recalculates meal totals.

---

### 5. Exercises

#### GET /exercises
🔒 **Requires Authentication**

List user's exercises.

**Query Parameters:**
- `date` (optional): Filter by date (YYYY-MM-DD)
- `type` (optional): Filter by type (`cardio`, `strength`, `flexibility`, `sports`)
- `page` (optional): Page number

---

#### POST /exercises
🔒 **Requires Authentication**

Log a new exercise.

**Request Body:**
```json
{
    "exercise_name": "Bench Press",
    "exercise_type": "strength",
    "duration_minutes": null,
    "calories_burned": 180,
    "sets": 4,
    "reps": 10,
    "weight": 80,
    "exercise_time": "2026-05-05 10:00:00"
}
```

**Field Constraints:**
- `exercise_type`: `cardio`, `strength`, `flexibility`, `sports`

**Response (201):** The created exercise object.

---

#### GET /exercises/{id}
🔒 **Requires Authentication**

#### PUT /exercises/{id}
🔒 **Requires Authentication**

#### DELETE /exercises/{id}
🔒 **Requires Authentication**

---

### 6. Workouts

#### GET /workouts
🔒 **Requires Authentication**

List user's workouts with sets and exercises.

---

#### POST /workouts
🔒 **Requires Authentication**

Create a new workout session.

**Request Body:**
```json
{
    "name": "Push Day",
    "started_at": "2026-05-05 10:00:00",
    "notes": "Feeling strong today"
}
```

**Response (201):** The created workout object.

---

#### GET /workouts/{id}
🔒 **Requires Authentication**

Get a workout with all sets and exercise details.

**Response (200):**
```json
{
    "id": 1,
    "name": "Push Day",
    "started_at": "2026-05-05T10:00:00.000000Z",
    "ended_at": "2026-05-05T11:15:00.000000Z",
    "total_volume": "12500.00",
    "total_duration_minutes": 75,
    "workout_sets": [
        {
            "id": 1,
            "set_number": 1,
            "reps": 10,
            "weight": "80.00",
            "rpe": 7,
            "rest_time_seconds": 90,
            "is_superset": false,
            "exercise": {
                "id": 1,
                "exercise_name": "Bench Press",
                "exercise_type": "strength"
            }
        }
    ]
}
```

---

#### PUT /workouts/{id}
🔒 **Requires Authentication**

#### DELETE /workouts/{id}
🔒 **Requires Authentication**

---

### 7. Workout Sets

#### POST /workouts/{workout_id}/sets
🔒 **Requires Authentication**

Add a set to a workout.

**Request Body:**
```json
{
    "exercise_id": 1,
    "set_number": 1,
    "reps": 10,
    "weight": 80,
    "rpe": 7,
    "rest_time_seconds": 90,
    "is_superset": false
}
```

---

#### DELETE /workouts/{workout_id}/sets/{set_id}
🔒 **Requires Authentication**

---

### 8. Weight Logs

#### GET /weight-logs
🔒 **Requires Authentication**

List weight log history (paginated, newest first).

---

#### POST /weight-logs
🔒 **Requires Authentication**

Log a new weight entry.

**Request Body:**
```json
{
    "weight": 79.5,
    "notes": "After morning workout",
    "logged_at": "2026-05-05 07:00:00"
}
```

---

#### DELETE /weight-logs/{id}
🔒 **Requires Authentication**

---

### 9. Photo Gallery

#### GET /photo-galleries
🔒 **Requires Authentication**

List progress photos (paginated, newest first).

---

#### POST /photo-galleries
🔒 **Requires Authentication**

Upload a progress photo.

**Request Body (multipart/form-data):**
- `photo` (file, required): Image file (max 5MB)
- `caption` (string, optional): Photo description
- `taken_at` (date, optional): When the photo was taken

---

#### DELETE /photo-galleries/{id}
🔒 **Requires Authentication**

---

### 10. Tutorial Videos

#### GET /tutorial-videos
🌐 **Public**

List tutorial videos with optional filters.

**Query Parameters:**
- `gender` (optional): `boy`, `girl`, `both`
- `muscle_group` (optional): `chest`, `back`, `shoulders`, `arms`, `legs`, `core`, `full_body`
- `page` (optional): Page number

---

#### GET /tutorial-videos/{id}
🌐 **Public**

Get a specific tutorial video.

**Response (200):**
```json
{
    "id": 1,
    "title": "Proper Squat Form",
    "description": "Learn correct squat technique...",
    "video_url": "https://example.com/videos/squat.mp4",
    "thumbnail_url": "https://example.com/thumbs/squat.jpg",
    "gender_target": "both",
    "muscle_group": "legs"
}
```

---

### 11. Support Messages

#### GET /support-messages
🔒 **Requires Authentication**

List user's support tickets.

---

#### POST /support-messages
🔒 **Requires Authentication**

Submit a new support ticket.

**Request Body:**
```json
{
    "subject": "Cannot sync data",
    "message": "I'm having trouble syncing my workout data between devices."
}
```

---

### 12. AI Features

#### POST /ai/meal/analyze
🔒 **Requires Authentication**

Analyze a meal photo using AI to identify foods and estimate nutrition.

**Request Body (multipart/form-data):**
- `image` (file, required): Meal photo (max 10MB)
- `meal_type` (string, optional): `breakfast`, `lunch`, `dinner`, `snack`

**Response (200):**
```json
{
    "analysis": {
        "foods": [
            {
                "food_name": "Grilled Chicken",
                "serving_size": 150,
                "serving_unit": "g",
                "calories": 248,
                "protein": 46.5,
                "carbs": 0,
                "fat": 5.4,
                "fiber": 0,
                "sugar": 0,
                "sodium": 120
            },
            {
                "food_name": "Brown Rice",
                "serving_size": 200,
                "serving_unit": "g",
                "calories": 218,
                "protein": 5,
                "carbs": 45.8,
                "fat": 1.6,
                "fiber": 3.5,
                "sugar": 0.7,
                "sodium": 5
            }
        ],
        "meal_name": "lunch",
        "confidence": 0.85
    },
    "image_path": "meal-scans/abc123.jpg"
}
```

**Error (429):**
```json
{
    "message": "Meal scan is temporarily unavailable because the AI provider is rate limited. Please retry shortly.",
    "provider": "mistral"
}
```

---

#### POST /ai/workout/generate
🔒 **Requires Authentication**

Generate a personalized workout plan using AI.

The AI is constrained to the admin-managed exercise catalog. Each returned exercise is matched to an admin exercise name and includes its `exercise_tutorial_url` for the mobile app.

**Request Body:**
```json
{
    "fitness_level": "intermediate",
    "primary_goal": "build muscle",
    "duration_minutes": 45,
    "available_equipment": "dumbbells, resistance bands",
    "focus_area": "upper body"
}
```

**Required Fields:**
- `fitness_level`
- `primary_goal`
- `duration_minutes`
- `available_equipment`
- `focus_area`

**Prerequisite:**
- At least one admin-managed exercise with an `exercise_tutorial_url` must exist, otherwise workout generation returns `422`

**Response (200):**
```json
{
    "workout_name": "Upper Body Strength Builder",
    "estimated_duration_minutes": 45,
    "exercises": [
        {
            "exercise_name": "Dumbbell Bench Press",
            "exercise_type": "strength",
            "exercise_tutorial_url": "https://tenor.com/view/dumbbell-bench-press-demo",
            "sets": 4,
            "reps": 10,
            "weight_kg": 22.5,
            "rest_time_seconds": 90,
            "rpe": 8,
            "calories_burned": 55,
            "notes": "Keep your shoulder blades retracted throughout the set."
        }
    ],
    "total_estimated_calories": 240,
    "notes": "Warm up with band pull-aparts and light pressing before the first working set."
}
```

**Error (422):**
```json
{
    "message": "Workout generation requires admin-managed exercises with tutorial URLs."
}
```

---

#### POST /ai/coach/chat
🔒 **Requires Authentication**

Chat with the AI fitness coach. The coach can read the user's profile, meal history, exercise
history and strength progress through tools, and a compact profile summary is included in every
prompt so the first reply is already personal.

**Request Body (JSON, or multipart/form-data when attaching a photo):**
```json
{
    "message": "Am I getting stronger on bench?",
    "conversation_id": "0192f0c4-... (optional, continues an existing chat)"
}
```

- `message` is required unless an `image` file is sent.
- `image` (multipart only): optional photo, max 10MB, stored under `coach-images`.

**Response (200):**
```json
{
    "message": "Your bench estimated 1RM went from 78 kg to 84 kg in six weeks...",
    "conversation_id": "0192f0c4-...",
    "title": "Bench progress review",
    "usage": { "prompt_tokens": 812, "completion_tokens": 190 }
}
```

`conversation_id` is the id to send on the next turn. If the AI provider is unavailable the endpoint
still returns 200 with a short fallback message and the **same** `conversation_id` it was given, so
the chat can continue later.

**Error (404):** the `conversation_id` belongs to another account.

---

#### GET /ai/coach/conversations
🔒 **Requires Authentication**

List the user's saved conversations, most recently active first.

**Response (200):**
```json
{
    "data": [
        {
            "id": "0192f0c4-...",
            "title": "Bench progress review",
            "preview": "Your bench estimated 1RM went from 78 kg...",
            "messages_count": 6,
            "created_at": "2026-10-20T08:00:00.000000Z",
            "updated_at": "2026-10-23T06:10:00.000000Z"
        }
    ],
    "meta": { "current_page": 1, "last_page": 1, "total": 1 }
}
```

---

#### GET /ai/coach/conversations/{id}
🔒 **Requires Authentication**

Reopen one conversation. Messages are returned oldest first; `image_url` is present on a user
message that had a photo attached, and points at `/media/coach-images/...`.

**Response (200):**
```json
{
    "id": "0192f0c4-...",
    "title": "Form check",
    "messages": [
        {
            "id": "0192f0c5-...",
            "role": "user",
            "content": "How is my setup?",
            "image_url": "https://host/api/v1/media/coach-images/abc.jpg",
            "created_at": "2026-10-23T06:00:00.000000Z"
        },
        {
            "id": "0192f0c6-...",
            "role": "assistant",
            "content": "Bar is a little forward.",
            "image_url": null,
            "created_at": "2026-10-23T06:00:05.000000Z"
        }
    ],
    "updated_at": "2026-10-23T06:00:05.000000Z"
}
```

**Error (404):** not the user's conversation.

---

#### DELETE /ai/coach/conversations/{id}
🔒 **Requires Authentication**

Delete a conversation, its messages and any images it attached.

**Response (200):**
```json
{ "message": "Conversation deleted." }
```


---

### 13. Camera Trainer Sessions

The camera trainer counts reps on the device (ML Kit pose detection). Every
finished session is written to SQLite first and uploaded here when there is a
connection, so training works offline.

`client_id` is generated on the phone and is the idempotency key: replaying the
same session after a dropped response updates the existing row instead of
creating a duplicate.

#### POST /camera-sessions
🔒 **Requires Authentication**

**Body:**
```json
{
    "client_id": "1761207600123456-9f3c1a7b",
    "exercise_type": "strength",
    "exercise_name": "Push-Up",
    "started_at": "2026-10-23T18:30:00+00:00",
    "ended_at": "2026-10-23T18:42:00+00:00",
    "duration_seconds": 720,
    "sets_count": 3,
    "total_reps": 24,
    "avg_form_score": 82,
    "sets": [
        { "set_number": 1, "reps": 9, "form_score": 80 },
        { "set_number": 2, "reps": 8, "form_score": 84, "rest_time_seconds": 62 }
    ],
    "source": "pose_tracker"
}
```

`exercise_type` is one of `strength`, `cardio`, `flexibility`, `balance`.
`avg_form_score` is 0-100 and optional (absent when the camera never saw the
body clearly). `rest_time_seconds` on a set is the rest that preceded it.

**Response (201 created / 200 updated):**
```json
{
    "message": "Camera session saved.",
    "session": {
        "id": 12,
        "user_id": 3,
        "client_id": "1761207600123456-9f3c1a7b",
        "exercise_type": "strength",
        "exercise_name": "Push-Up",
        "started_at": "2026-10-23T18:30:00.000000Z",
        "ended_at": "2026-10-23T18:42:00.000000Z",
        "duration_seconds": 720,
        "sets_count": 3,
        "total_reps": 24,
        "avg_form_score": 82,
        "sets": [ ... ],
        "source": "pose_tracker",
        "created_at": "2026-10-23T18:42:03.000000Z",
        "updated_at": "2026-10-23T18:42:03.000000Z"
    }
}
```

#### GET /camera-sessions
🔒 **Requires Authentication**

Paginated, newest first, 30 per page.

#### GET /camera-sessions/stats
🔒 **Requires Authentication**

```json
{
    "totals": { "sessions": 14, "reps": 312, "seconds": 8400 },
    "this_week": 3,
    "personal_bests": [
        {
            "exercise_name": "Push-Up",
            "best_reps": 34,
            "best_form_score": 91,
            "sessions": 6
        }
    ]
}
```


---

## Error Responses

All errors follow Laravel's standard format:

**Validation Error (422):**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "field_name": ["Error message"]
    }
}
```

**Unauthorized (401):**
```json
{
    "message": "Unauthenticated."
}
```

**Forbidden (403):**
```json
{
    "message": "This action is unauthorized."
}
```

**Not Found (404):**
```json
{
    "message": "No query results for model [App\\Models\\Meal] 999"
}
```

---

## Pagination

Paginated endpoints return:
```json
{
    "current_page": 1,
    "data": [...],
    "first_page_url": "http://localhost:8000/api/v1/meals?page=1",
    "from": 1,
    "last_page": 3,
    "last_page_url": "http://localhost:8000/api/v1/meals?page=3",
    "next_page_url": "http://localhost:8000/api/v1/meals?page=2",
    "per_page": 20,
    "prev_page_url": null,
    "to": 20,
    "total": 55
}
```

---

## Rate Limiting

API endpoints are rate-limited to 60 requests per minute per user. The following headers are included in responses:
- `X-RateLimit-Limit`
- `X-RateLimit-Remaining`
