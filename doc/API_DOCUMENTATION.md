# GYM APP - API Documentation

## Base URL
```
http://localhost:8000/api/v1
```

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
    "meal_time": "2026-05-05 08:00:00"
}
```

**Field Constraints:**
- `name`: `breakfast`, `lunch`, `dinner`, `snack`

**Response (201):** The created meal object.

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
    "photo_path": null,
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

Chat with the AI fitness coach. Supports conversation history.

**Request Body:**
```json
{
    "message": "What should I eat after my workout?",
    "conversation_id": "optional-uuid-for-continuing-conversation"
}
```

**Response (200):**
```json
{
    "message": "After your workout, aim to eat within 30-60 minutes...",
    "conversation_id": "uuid-of-conversation"
}
```

---

#### GET /ai/coach/conversations
🔒 **Requires Authentication**

List previous coaching conversations.

**Response (200):** Paginated list of conversations.

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
