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
        Route::get('{id}/availability', [TutorController::class, 'getPublicAvailability']);
        Route::get('{id}/busy-slots', [TutorController::class, 'getBusySlots']);
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
            ->orderBy('city')
            ->get();

        return response()->json([
            'locations' => $locations
        ]);
    });

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {

        // Tutor-specific routes
        Route::prefix('tutor')->group(function () {
            Route::get('dashboard', [TutorController::class, 'dashboard']);
            Route::get('schedule', [TutorController::class, 'getWeekSchedule']);
            Route::get('profile', [TutorController::class, 'getProfile']);
            Route::post('profile', [TutorController::class, 'updateProfile']);
            Route::put('profile', [TutorController::class, 'updateProfile']);
            Route::get('availability', [TutorController::class, 'getAvailability']);
            Route::post('availability', [TutorController::class, 'updateAvailability']);
            Route::put('availability', [TutorController::class, 'updateAvailability']);
            Route::get('bookings', [TutorController::class, 'getBookings']);
            Route::get('reviews', [TutorController::class, 'getReviews']);
            Route::get('earnings', [TutorController::class, 'getEarnings']);
        });

        // Student-specific routes
        Route::prefix('student')->group(function () {
        Route::get('dashboard', [StudentController::class, 'getDashboard']);
        Route::get('bookings', [StudentController::class, 'getBookings']);
        Route::get('profile', [StudentController::class, 'getProfile']);
        Route::post('profile', [StudentController::class, 'updateProfile']);
        Route::put('profile', [StudentController::class, 'updateProfile']);
    });

        // Booking routes
        Route::prefix('bookings')->group(function () {
        Route::get('/', [BookingController::class, 'index']);        // List bookings
        Route::post('/', [BookingController::class, 'store']);       // Create booking
        Route::get('{id}', [BookingController::class, 'show']);      // Show booking
        Route::put('{id}', [BookingController::class, 'update']);    // THIS IS THE KEY ROUTE
        Route::patch('{id}', [BookingController::class, 'update']);  // Alternative method
        Route::patch('{id}/confirm', [BookingController::class, 'confirm']);
        Route::patch('{id}/complete', [BookingController::class, 'complete']);
        Route::patch('{id}/cancel', [BookingController::class, 'cancel']);
        Route::patch('{id}/confirm-payment', [BookingController::class, 'confirmPayment']);
    });

        // Review routes
        Route::prefix('reviews')->group(function () {
        Route::post('/', [ReviewController::class, 'store']);           // Create review
        Route::get('{id}', [ReviewController::class, 'show']);          // Get review by ID
        Route::put('{id}', [ReviewController::class, 'update']);        // Update review
        Route::delete('{id}', [ReviewController::class, 'destroy']);    // Delete review
        Route::post('{review}/reply', [ReviewController::class, 'reply']); // Reply to review
    });

        // Payment routes
        Route::prefix('payments')->group(function () {
            Route::patch('{id}/confirm-cash', [BookingController::class, 'confirmCashPayment']);
        });
    });
});

// Fallback route for API
Route::fallback(function () {
    return response()->json([
        'message' => 'API endpoint not found'
    ], 404);
});
