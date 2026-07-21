<?php

use App\Http\Controllers\Api\V1\AiCoachController;
use App\Http\Controllers\Api\V1\AiMealController;
use App\Http\Controllers\Api\V1\AiWorkoutController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ExerciseController;
use App\Http\Controllers\Api\V1\FoodLogController;
use App\Http\Controllers\Api\V1\MealController;
use App\Http\Controllers\Api\V1\PhotoGalleryController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\SupportMessageController;
use App\Http\Controllers\Api\V1\TutorialVideoController;
use App\Http\Controllers\Api\V1\WeightLogController;
use App\Http\Controllers\Api\V1\WorkoutController;
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

        // Exercises
        Route::apiResource('exercises', ExerciseController::class);

        // Workouts & Sets
        Route::apiResource('workouts', WorkoutController::class);
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
