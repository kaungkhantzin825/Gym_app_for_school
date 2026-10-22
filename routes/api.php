<?php

use App\Http\Controllers\Api\V1\AiCoachController;
use App\Http\Controllers\Api\V1\AiMealController;
use App\Http\Controllers\Api\V1\AiWorkoutController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ExerciseController;
use App\Http\Controllers\Api\V1\FoodLogController;
use App\Http\Controllers\Api\V1\GuestProfileController;
use App\Http\Controllers\Api\V1\MealController;
use App\Http\Controllers\Api\V1\PhotoGalleryController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ProgressController;
use App\Http\Controllers\Api\V1\SupportMessageController;
use App\Http\Controllers\Api\V1\TutorialVideoController;
use App\Http\Controllers\Api\V1\WeightLogController;
use App\Http\Controllers\Api\V1\WorkoutController;
use App\Http\Controllers\Api\V1\WorkoutSessionController;
use App\Http\Controllers\Api\V1\WorkoutSetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Public tutorial videos
    Route::apiResource('tutorial-videos', TutorialVideoController::class)->only(['index', 'show']);

    // Guest profile (for non-logged in users by device/app_id)
    Route::post('/guest/profile', [GuestProfileController::class, 'store']);
    Route::get('/guest/profile/{app_id}', [GuestProfileController::class, 'show']);

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::put('/password', [AuthController::class, 'changePassword']);

        // Profile
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto']);

        // Meals & Food Logs
        Route::apiResource('meals', MealController::class);
        Route::post('/meals/{meal}/food-logs', [FoodLogController::class, 'store']);
        Route::delete('/meals/{meal}/food-logs/{foodLog}', [FoodLogController::class, 'destroy']);

        
        Route::apiResource('exercises', ExerciseController::class);

        // Workouts & Sets
        // One-shot write for a finished live session (workout + exercises +
        // every set). Must be declared before the resource so `session` is not
        // mistaken for a workout id.
        Route::post('/workouts/session', [WorkoutSessionController::class, 'store']);
        Route::apiResource('workouts', WorkoutController::class);

        // Progress analytics derived from the saved sets.
        Route::get('/progress', [ProgressController::class, 'index']);
        Route::post('/workouts/{workout}/sets', [WorkoutSetController::class, 'store']);
        Route::delete('/workouts/{workout}/sets/{workoutSet}', [WorkoutSetController::class, 'destroy']);

        // Weight Logs
        Route::apiResource('weight-logs', WeightLogController::class)->only(['index', 'store', 'destroy']);

        // Photo Gallery
        Route::apiResource('photo-galleries', PhotoGalleryController::class)->only(['index', 'store', 'destroy']);

        // Support Messages
        Route::apiResource('support-messages', SupportMessageController::class)->only(['index', 'store']);

        // AI Features
        Route::prefix('ai')->group(function () {
            Route::post('/meal/analyze', [AiMealController::class, 'analyze']);
            Route::post('/workout/generate', [AiWorkoutController::class, 'generate']);
            Route::post('/coach/chat', [AiCoachController::class, 'chat']);
            Route::get('/coach/conversations', [AiCoachController::class, 'conversations']);
        });
    });
});
