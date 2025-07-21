<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TutorController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ReminderController;
use App\Http\Controllers\Api\NotificationPreferenceController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\AdController;

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

    // Public test ads route (for development)
    Route::get('test-ads', function () {
        return response()->json([
            'message' => 'Public ads endpoint working',
            'ads' => [
                [
                    'id' => 1,
                    'title' => 'Test Public Ad',
                    'description' => 'This is a test ad from the public endpoint',
                    'image_url' => 'https://via.placeholder.com/400x200/3B82F6/FFFFFF?text=Test',
                    'click_url' => 'https://example.com',
                    'type' => 'banner',
                    'placement' => 'header'
                ]
            ],
            'should_show_ads' => true
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
            Route::get('/', [BookingController::class, 'index']);
            Route::post('/', [BookingController::class, 'store']);
            Route::get('{id}', [BookingController::class, 'show']);
            Route::put('{id}', [BookingController::class, 'update']);
            Route::patch('{id}', [BookingController::class, 'update']);
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
            Route::post('{review}/reply', [ReviewController::class, 'reply']);
        });

        // Payment routes
        Route::prefix('payments')->group(function () {
            Route::patch('{id}/confirm-cash', [BookingController::class, 'confirmCashPayment']);
        });

        // Reminder routes
        Route::prefix('reminders')->group(function () {
            Route::get('/', [ReminderController::class, 'index']);
            Route::post('{id}/mark-read', [ReminderController::class, 'markAsRead']);
            Route::post('mark-all-read', [ReminderController::class, 'markAllAsRead']);
            Route::get('unread-count', [ReminderController::class, 'getUnreadCount']);
        });

        // Notification preferences routes
        Route::prefix('notification-preferences')->group(function () {
            Route::get('/', [NotificationPreferenceController::class, 'show']);
            Route::put('/', [NotificationPreferenceController::class, 'update']);
        });

        // Subscription routes
        Route::prefix('subscription')->group(function () {
            Route::get('/', [SubscriptionController::class, 'index']);
            Route::post('upgrade', [SubscriptionController::class, 'upgrade']);
            Route::post('cancel', [SubscriptionController::class, 'cancel']);
            Route::get('plans', [SubscriptionController::class, 'plans']);
        });

        // Payment routes
        Route::prefix('payment')->group(function () {
            Route::post('subscription', [PaymentController::class, 'createSubscriptionPayment']);
            Route::get('{paymentId}/status', [PaymentController::class, 'getPaymentStatus']);
        });

        Route::prefix('messages')->group(function () {
            Route::get('/', [MessageController::class, 'index']); // Get user's conversations
            Route::post('/', [MessageController::class, 'store']); // Send a message
            Route::post('start-conversation', [MessageController::class, 'startConversation']); // Start new conversation
            Route::get('unread-count', [MessageController::class, 'getUnreadCount']); // Get unread messages count
            Route::get('{conversation}', [MessageController::class, 'show']); // Get conversation messages
            Route::patch('{conversation}/read', [MessageController::class, 'markAsRead']); // Mark conversation as read
        });

        // Ad routes - FIXED: Correct route structure
        Route::get('ads', function (Request $request) {
            // Log what we're receiving for debugging
            \Log::info('Ads API called with params:', $request->all());

            $testAds = [
    // Banner ads
    [
        'id' => 1,
        'title' => 'Îmbunătățește-ți notele la Matematică',
        'description' => 'Tutorii noștri experți te ajută să înțelegi conceptele dificile și să obții rezultate excelente la matematică.',
        'image_url' => 'https://via.placeholder.com/400x200/3B82F6/FFFFFF?text=Matematica',
        'click_url' => 'https://example.com/math-tutoring',
        'type' => 'banner',
        'placement' => 'header',
        'priority' => 10,
        'impressions' => 1250,
        'clicks' => 45
    ],
    [
        'id' => 2,
        'title' => 'Laborator de Științe Interactive',
        'description' => 'Descoperă știința prin experimente practice și sesiuni interactive cu experți.',
        'image_url' => 'https://via.placeholder.com/400x250/8B5CF6/FFFFFF?text=Stiinte',
        'click_url' => 'https://example.com/science-lab',
        'type' => 'inline',
        'placement' => 'feed',
        'priority' => 9,
        'impressions' => 1580,
        'clicks' => 67
    ],
    // Sidebar ads
    [
        'id' => 3,
        'title' => 'Cursuri de Engleză Online',
        'description' => 'Învață engleza cu vorbitori nativi și obține certificarea de care ai nevoie.',
        'image_url' => 'https://via.placeholder.com/300x200/10B981/FFFFFF?text=Engleza',
        'click_url' => 'https://example.com/english-course',
        'type' => 'card',
        'placement' => 'sidebar',
        'priority' => 8,
        'impressions' => 890,
        'clicks' => 32
    ],
    [
        'id' => 4,
        'title' => 'Programare pentru Începători',
        'description' => 'Învață să programezi cu instructori experimentați. Python, JavaScript și multe altele!',
        'image_url' => 'https://via.placeholder.com/300x200/EF4444/FFFFFF?text=Programare',
        'click_url' => 'https://example.com/programming',
        'type' => 'card',
        'placement' => 'sidebar',
        'priority' => 6,
        'impressions' => 756,
        'clicks' => 28
    ],
    // Bottom ad
    [
        'id' => 5,
        'title' => 'Pregătire Examene Naționale',
        'description' => 'Obține rezultate excelente la Bacalaureat și Evaluarea Națională cu ajutorul tutorilor specializați.',
        'image_url' => 'https://via.placeholder.com/500x200/F59E0B/FFFFFF?text=Examene+Nationale',
        'click_url' => 'https://example.com/examene',
        'type' => 'banner',
        'placement' => 'bottom',
        'priority' => 7,
        'impressions' => 945,
        'clicks' => 38
    ]
];

            // Filter ads based on request parameters
            $placement = $request->input('placement');
            $type = $request->input('type');
            $limit = $request->input('limit', 3);

            $filteredAds = $testAds;

            // Apply filters if provided
            if ($placement) {
                $filteredAds = array_filter($filteredAds, function($ad) use ($placement) {
                    return $ad['placement'] === $placement;
                });
            }

            if ($type) {
                $filteredAds = array_filter($filteredAds, function($ad) use ($type) {
                    return $ad['type'] === $type;
                });
            }

            // Re-index array and apply limit
            $filteredAds = array_values($filteredAds);
            $filteredAds = array_slice($filteredAds, 0, $limit);

            return response()->json([
                'ads' => $filteredAds,
                'should_show_ads' => true,
                'user_type' => 'student',
                'total' => count($filteredAds),
                'filters_applied' => [
                    'placement' => $placement,
                    'type' => $type,
                    'limit' => $limit
                ],
                'debug' => [
                    'total_test_ads' => count($testAds),
                    'after_filters' => count($filteredAds),
                    'request_params' => $request->all()
                ]
            ]);
        });

        // Ad click tracking route
        Route::post('ads/{adId}/click', function ($adId) {
            \Log::info('Ad click recorded', ['ad_id' => $adId]);

            return response()->json([
                'message' => 'Click recorded successfully',
                'ad_id' => $adId,
                'success' => true,
                'timestamp' => now()->toISOString()
            ]);
        });

        // Ad impression tracking route (optional)
        Route::post('ads/{adId}/impression', function ($adId) {
            \Log::info('Ad impression recorded', ['ad_id' => $adId]);

            return response()->json([
                'message' => 'Impression recorded successfully',
                'ad_id' => $adId,
                'success' => true,
                'timestamp' => now()->toISOString()
            ]);
        });
    });

    // Public webhook route (no auth required)
    Route::post('payment/webhook', [PaymentController::class, 'webhook']);
    Route::get('payment/{paymentId}/success', [PaymentController::class, 'success'])->name('payment.success');
});

// Fallback route for API
Route::fallback(function () {
    return response()->json([
        'message' => 'API endpoint not found',
        'available_endpoints' => [
            'GET /api/v1/ads - Get advertisements (authenticated)',
            'GET /api/v1/test-ads - Test ads endpoint (public)',
            'POST /api/v1/ads/{id}/click - Record ad click (authenticated)',
            'POST /api/v1/ads/{id}/impression - Record ad impression (authenticated)'
        ]
    ], 404);
});
