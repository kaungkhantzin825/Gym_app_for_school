# GYM APP - Database Schema Design

## Overview
This document describes the complete database schema for the Gym App, a comprehensive fitness and nutrition tracking application with AI-powered features.


---

## Entity Relationship Diagram (Text Format)

```
┌─────────────────┐
│     USERS       │
│─────────────────│
│ id (PK)         │
│ name            │
│ email (UQ)      │
│ phone (UQ)      │
│ profile_photo   │
│ password        │
│ role            │
│ fcm_token       │
│ device_type     │
└────────┬────────┘
         │
         ├──────────────────────────────────────────────┐
         │                                              │
         │ 1:1                                          │ 1:N
         ▼                                              ▼
┌─────────────────┐                          ┌──────────────────┐
│ USER_PROFILES   │                          │  WEIGHT_LOGS     │
│─────────────────│                          │──────────────────│
│ id (PK)         │                          │ id (PK)          │
│ user_id (FK)    │                          │ user_id (FK)     │
│ age             │                          │ weight           │
│ gender          │                          │ notes            │
│ height          │                          │ logged_at        │
│ current_weight  │                          └──────────────────┘
│ target_weight   │
│ goal            │                          ┌──────────────────┐
│ activity_level  │                          │ PHOTO_GALLERIES  │
│ daily_targets   │                          │──────────────────│
└─────────────────┘                          │ id (PK)          │
                                             │ user_id (FK)     │
         ┌───────────────────────────────────│ photo_url        │
         │ 1:N                               │ caption          │
         ▼                                   │ taken_at         │
┌─────────────────┐                          └──────────────────┘
│     MEALS       │
│─────────────────│                          ┌──────────────────┐
│ id (PK)         │                          │   EXERCISES      │
│ user_id (FK)    │                          │──────────────────│
│ name            │                          │ id (PK)          │
│ photo_path      │                          │ user_id (FK)     │
│ notes           │                          │ exercise_name    │
│ total_calories  │                          │ exercise_type    │
│ total_protein   │                          │ duration_minutes │
│ total_carbs     │                          │ calories_burned  │
│ total_fat       │                          │ sets, reps       │
│ meal_time       │                          │ weight           │
│ deleted_at      │                          │ exercise_time    │
└────────┬────────┘                          │ deleted_at       │
         │                                   └───────┬──────────┘
         │ 1:N                                       │
         ▼                                           │ 1:N
┌─────────────────┐                                  │
│   FOOD_LOGS     │                                  │
│─────────────────│                                  │
│ id (PK)         │                          ┌───────▼──────────┐
│ meal_id (FK)    │                          │    WORKOUTS      │
│ food_name       │                          │──────────────────│
│ fatsecret_id    │                          │ id (PK)          │
│ brand_name      │                          │ user_id (FK)     │
│ serving_size    │                          │ name             │
│ serving_unit    │                          │ started_at       │
│ calories        │                          │ ended_at         │
│ protein         │                          │ notes            │
│ carbs, fat      │                          │ total_volume     │
│ fiber, sugar    │                          │ total_duration   │
│ sodium          │                          │ deleted_at       │
└─────────────────┘                          └────────┬─────────┘
                                                      │
                                                      │ 1:N
                                                      ▼
                                             ┌──────────────────┐
                                             │  WORKOUT_SETS    │
                                             │──────────────────│
                                             │ id (PK)          │
                                             │ workout_id (FK)  │
                                             │ exercise_id (FK) │◄─┐
                                             │ set_number       │  │
                                             │ reps             │  │
                                             │ weight           │  │
                                             │ rpe              │  │
                                             │ rest_time_sec    │  │
                                             │ is_superset      │  │
                                             └──────────────────┘  │
                                                                   │
                                                                   │
                                            ┌─-────────────────────┘
                                            │
┌─────────────────┐                         │
│ TUTORIAL_VIDEOS │                         │
│─────────────────│                         │
│ id (PK)         │                         │
│ title           │                         │
│ description     │                         │
│ video_url       │                         │
│ thumbnail_url   │                         │
│ gender_target   │                         │
│ muscle_group    │                         │
└─────────────────┘                         │
                                            │
┌─────────────────┐                         │
│ SUPPORT_MSGS    │                         │
│─────────────────│                         │
│ id (PK)         │                         │
│ user_id (FK)    │◄────────────────────────┘
│ subject         │
│ message         │
│ status          │
│ admin_reply     │
│ replied_at      │
└─────────────────┘

┌─────────────────┐
│  APP_SETTINGS   │
│─────────────────│
│ id (PK)         │
│ key (UQ)        │
│ value           │
└─────────────────┘
```

---

## Tables Description

### 1. **users**
Core authentication and user management table.

**Key Fields:**
- `id`: Primary key
- `email`, `phone`: Multiple authentication methods (unique)
- `role`: User role (user/admin)
- `fcm_token`, `device_type`: Push notification support
- `profile_photo`: User avatar

**Relationships:**
- 1:1 with `user_profiles`
- 1:N with `meals`, `exercises`, `workouts`, `weight_logs`, `photo_galleries`, `support_messages`

---

### 2. **user_profiles**
Stores user health metrics and fitness goals.

**Key Fields:**
- `age`, `gender`, `height`: Demographics
- `current_weight`, `target_weight`: Weight tracking
- `goal`: Fitness objective (lose_weight, gain_weight, maintain, build_muscle)
- `activity_level`: Exercise frequency (sedentary to very_active)
- `daily_*_target`: Nutrition targets (calories, protein, carbs, fat)

**Relationships:**
- N:1 with `users` (one profile per user)

---

### 3. **meals**
Meal entries with aggregated nutritional data.

**Key Fields:**
- `name`: Meal type (breakfast, lunch, dinner, snack)
- `photo_path`: AI-scanned meal photo
- `total_*`: Aggregated nutrition (calories, protein, carbs, fat)
- `meal_time`: When meal was consumed
- `deleted_at`: Soft delete support

**Relationships:**
- N:1 with `users`
- 1:N with `food_logs`

---

### 4. **food_logs**
Individual food items within a meal.

**Key Fields:**
- `food_name`, `brand_name`: Food identification
- `fatsecret_food_id`: External API reference
- `serving_size`, `serving_unit`: Portion information
- `calories`, `protein`, `carbs`, `fat`: Macronutrients
- `fiber`, `sugar`, `sodium`: Micronutrients

**Relationships:**
- N:1 with `meals`

---

### 5. **exercises**
Individual exercise records (legacy/standalone).

**Key Fields:**
- `exercise_name`: Exercise identifier
- `exercise_type`: cardio, strength, flexibility, sports
- `duration_minutes`: For cardio exercises
- `sets`, `reps`, `weight`: For strength training
- `calories_burned`: Energy expenditure
- `deleted_at`: Soft delete support

**Relationships:**
- N:1 with `users`
- 1:N with `workout_sets` (can be referenced in structured workouts)

---

### 6. **workouts**
Structured workout sessions.

**Key Fields:**
- `name`: Workout identifier
- `started_at`, `ended_at`: Session duration
- `total_volume`: Total weight lifted (sets × reps × weight)
- `total_duration_minutes`: Workout length
- `deleted_at`: Soft delete support

**Relationships:**
- N:1 with `users`
- 1:N with `workout_sets`

---

### 7. **workout_sets**
Individual sets within a workout session.

**Key Fields:**
- `set_number`: Order within workout
- `reps`, `weight`: Performance metrics
- `rpe`: Rate of Perceived Exertion (1-10 scale)
- `rest_time_seconds`: Recovery time
- `is_superset`: Indicates superset grouping

**Relationships:**
- N:1 with `workouts`
- N:1 with `exercises`

---

### 8. **weight_logs**
Historical weight tracking.

**Key Fields:**
- `weight`: Body weight in kg
- `logged_at`: Measurement timestamp
- `notes`: Optional context

**Relationships:**
- N:1 with `users`

---

### 9. **photo_galleries**
Progress photos for visual tracking.

**Key Fields:**
- `photo_url`: Image storage path
- `caption`: Optional description
- `taken_at`: Photo date

**Relationships:**
- N:1 with `users`

---

### 10. **tutorial_videos**
Educational exercise content.

**Key Fields:**
- `title`, `description`: Video metadata
- `video_url`, `thumbnail_url`: Media paths
- `gender_target`: boy, girl, both
- `muscle_group`: Target muscle category

**Relationships:**
- Standalone (no foreign keys)

---

### 11. **support_messages**
User support ticket system.

**Key Fields:**
- `subject`, `message`: User inquiry
- `status`: pending, read, replied
- `admin_reply`: Support response
- `replied_at`: Response timestamp

**Relationships:**
- N:1 with `users`

---

### 12. **app_settings**
Application configuration key-value store.

**Key Fields:**
- `key`: Setting identifier (unique)
- `value`: Setting content (text)

**Default Settings:**
- `about`: App information
- `privacy_policy`: Privacy terms

**Relationships:**
- Standalone (no foreign keys)

---

## Indexes

### Performance Optimization Indexes:
- `users.email`, `users.role`
- `user_profiles.user_id` (unique)
- `weight_logs(user_id, logged_at)`
- `photo_galleries(user_id, taken_at)`
- `meals(user_id, meal_time)`, `meals.deleted_at`
- `food_logs.meal_id`, `food_logs.fatsecret_food_id`
- `exercises(user_id, exercise_time)`, `exercises.exercise_type`, `exercises.deleted_at`
- `workouts(user_id, started_at)`, `workouts.deleted_at`
- `workout_sets.workout_id`, `workout_sets.exercise_id`
- `tutorial_videos.gender_target`, `tutorial_videos.muscle_group`
- `support_messages(user_id, status)`, `support_messages.status`
- `app_settings.key`

---

## Data Integrity

### Foreign Key Constraints:
All foreign keys use `ON DELETE CASCADE` to maintain referential integrity:
- When a user is deleted, all related data is automatically removed
- When a meal is deleted, all food_logs are removed
- When a workout is deleted, all workout_sets are removed

### Soft Deletes:
Tables with `deleted_at` column support soft deletion:
- `meals`
- `exercises`
- `workouts`

This allows data recovery and maintains historical references.

---

## Key Features

### 1. **Multi-Auth Support**
- Email/password authentication

### 2. **Nutrition Tracking**
- AI-powered meal photo scanning
- Integration with FatSecret API
- Detailed macro/micronutrient tracking
- Daily target monitoring

### 3. **Workout Management**
- Structured workout sessions
- Exercise library
- Set-by-set tracking
- Volume and RPE metrics
- Superset support

### 4. **Progress Monitoring**
- Weight logging over time
- Photo gallery for visual progress
- Calorie and macro tracking
- Goal-based recommendations

### 5. **Push Notifications**
- FCM token storage
- Device type tracking (iOS/Android)

### 6. **Admin Features**
- Role-based access control
- Support ticket system
- App settings management
- Tutorial video management

---

## Data Types & Constraints

### Enums:
- `user_profiles.gender`: male, female, other
- `user_profiles.goal`: lose_weight, gain_weight, maintain, build_muscle
- `user_profiles.activity_level`: sedentary, light, moderate, active, very_active
- `exercises.exercise_type`: cardio, strength, flexibility, sports
- `tutorial_videos.gender_target`: boy, girl, both
- `support_messages.status`: pending, read, replied

### Decimal Precision:
- Weight measurements: DECIMAL(5, 2) - supports up to 999.99 kg
- Nutrition values: DECIMAL(8, 2) - supports large serving sizes
- Height: DECIMAL(5, 2) - supports up to 999.99 cm

---
