<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TutorController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes
Route::prefix('v1')->group(function () {

    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);

        // Protected auth routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('logout-all', [AuthController::class, 'logoutAll']);
            Route::post('refresh', [AuthController::class, 'refresh']);
        });
    });

    // Public tutor routes
    Route::prefix('tutors')->group(function () {
        Route::get('/', [TutorController::class, 'index']);
        Route::get('{id}', [TutorController::class, 'show']);
    });

    // Public data routes
    Route::get('subjects', function () {
        return response()->json([
            'subjects' => \App\Models\Subject::active()
                ->select('id', 'name', 'slug', 'description', 'icon')
                ->orderBy('name')
                ->get()
        ]);
    });

    Route::get('locations', function () {
        $locations = \App\Models\Location::active()
            ->select('id', 'city', 'county', 'slug')
            ->orderBy('county')
            ->orderBy('city')
            ->get();

        return response()->json([
            'locations' => $locations->groupBy('county'),
            'all_locations' => $locations, // Also provide flat list for easier filtering
        ]);
    });

    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {

        // Dashboard routes
        Route::prefix('dashboard')->group(function () {
            Route::get('stats', [DashboardController::class, 'stats']);
            Route::get('recent-activity', [DashboardController::class, 'recentActivity']);
        });

        // Tutor-specific routes (check user type in controller)
        Route::prefix('tutor')->group(function () {
            Route::get('dashboard', [TutorController::class, 'dashboard']);
            Route::put('profile', [TutorController::class, 'updateProfile']);
            Route::put('availability', [TutorController::class, 'updateAvailability']);
        });

        // Student-specific routes (check user type in controller)
        Route::prefix('student')->group(function () {
            Route::get('dashboard', [StudentController::class, 'dashboard']);
            Route::put('profile', [StudentController::class, 'updateProfile']);
        });

        // Booking routes (both tutors and students)
        Route::prefix('bookings')->group(function () {
            Route::get('/', [BookingController::class, 'index']);
            Route::post('/', [BookingController::class, 'store']);
            Route::get('{id}', [BookingController::class, 'show']);
            Route::patch('{id}/confirm', [BookingController::class, 'confirm']);
            Route::patch('{id}/complete', [BookingController::class, 'complete']);
            Route::patch('{id}/cancel', [BookingController::class, 'cancel']);
            Route::patch('{id}/confirm-payment', [BookingController::class, 'confirmPayment']);
        });

        // Review routes
        Route::prefix('reviews')->group(function () {
            Route::post('/', [ReviewController::class, 'store']);
            Route::get('{id}', [ReviewController::class, 'show']);
            Route::put('{id}', [ReviewController::class, 'update']);
            Route::delete('{id}', [ReviewController::class, 'destroy']);
            Route::post('{id}/reply', [ReviewController::class, 'reply']);
        });
    });
});

// Fallback route for API
Route::fallback(function () {
    return response()->json([
        'message' => 'API endpoint not found.',
        'status' => 404
    ], 404);
});
