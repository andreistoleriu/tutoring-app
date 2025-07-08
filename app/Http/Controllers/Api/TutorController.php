<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tutor;
use App\Models\Subject;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TutorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Tutor::with(['user', 'location', 'subjects'])
            ->where('is_verified', true);

        // Filter by subject
        if ($request->filled('subject')) {
            $query->whereHas('subjects', function ($q) use ($request) {
                $q->where('slug', $request->subject);
            });
        }

        // Filter by location
        if ($request->filled('location')) {
            $query->whereHas('location', function ($q) use ($request) {
                $q->where('slug', $request->location);
            });
        }

        // Filter by lesson type
        if ($request->filled('lesson_type')) {
            if ($request->lesson_type === 'online') {
                $query->where('offers_online', true);
            } elseif ($request->lesson_type === 'in_person') {
                $query->where('offers_in_person', true);
            }
        }

        // Filter by price range
        if ($request->filled('price_range')) {
            switch ($request->price_range) {
                case 'under_50':
                    $query->where('hourly_rate', '<', 50);
                    break;
                case '50_100':
                    $query->whereBetween('hourly_rate', [50, 100]);
                    break;
                case 'over_100':
                    $query->where('hourly_rate', '>', 100);
                    break;
            }
        }

        // Search by name or bio
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                })->orWhere('bio', 'like', "%{$search}%");
            });
        }

        // Sort
        switch ($request->get('sort_by')) {
            case 'price_low':
                $query->orderBy('hourly_rate', 'asc');
                break;
            case 'price_high':
                $query->orderBy('hourly_rate', 'desc');
                break;
            case 'rating':
                $query->orderBy('rating', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('rating', 'desc');
        }

        $perPage = min($request->get('per_page', 12), 50);
        $tutors = $query->paginate($perPage);

        return response()->json([
            'tutors' => $tutors->items(),
            'pagination' => [
                'current_page' => $tutors->currentPage(),
                'last_page' => $tutors->lastPage(),
                'per_page' => $tutors->perPage(),
                'total' => $tutors->total(),
            ],
            'filters' => [
                'subjects' => Subject::active()->select('id', 'name', 'slug')->get(),
                'locations' => Location::active()->select('id', 'city', 'county', 'slug')->get(),
            ],
        ]);
    }

    public function show($id): JsonResponse
    {
        $tutor = Tutor::with([
            'user',
            'location',
            'subjects',
            'reviews.student',
            'availabilities' => function ($q) {
                $q->where('is_active', true)->orderBy('day_of_week');
            }
        ])->findOrFail($id);

        // Update last active timestamp
        $tutor->update(['last_active' => now()]);

        return response()->json(['tutor' => $tutor]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isTutor()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $tutor = $user->tutor;

        if (!$tutor) {
            return response()->json(['message' => 'Tutor profile not found'], 404);
        }

        // Debug: Log the tutor data
        Log::info('Dashboard Debug - Tutor Data:', [
            'tutor_id' => $tutor->id,
            'total_earnings' => $tutor->total_earnings,
            'total_lessons' => $tutor->total_lessons,
            'rating' => $tutor->rating,
            'total_reviews' => $tutor->total_reviews,
        ]);

        // Get pending bookings
        $pendingBookings = $user->tutorBookings()
            ->with(['student', 'subject'])
            ->where('status', 'pending')
            ->orderBy('scheduled_at')
            ->get();

        Log::info('Dashboard Debug - Pending Bookings Count:', ['count' => $pendingBookings->count()]);

        $pendingBookingsData = $pendingBookings->map(function ($booking) {
            return [
                'id' => $booking->id,
                'student' => [
                    'full_name' => $booking->student->full_name,
                    'email' => $booking->student->email,
                ],
                'subject' => [
                    'name' => $booking->subject->name,
                ],
                'scheduled_at' => $booking->scheduled_at->toISOString(),
                'lesson_type' => $booking->lesson_type,
                'price' => (float) $booking->price,
                'status' => $booking->status,
                'payment_method' => $booking->payment_method,
                'student_notes' => $booking->student_notes,
            ];
        });

        // Get upcoming confirmed bookings
        $upcomingBookings = $user->tutorBookings()
            ->with(['student', 'subject'])
            ->where('scheduled_at', '>', now())
            ->where('status', 'confirmed')
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get();

        $upcomingBookingsData = $upcomingBookings->map(function ($booking) {
            return [
                'id' => $booking->id,
                'student' => [
                    'full_name' => $booking->student->full_name,
                    'email' => $booking->student->email,
                ],
                'subject' => [
                    'name' => $booking->subject->name,
                ],
                'scheduled_at' => $booking->scheduled_at->toISOString(),
                'lesson_type' => $booking->lesson_type,
                'price' => (float) $booking->price,
                'status' => $booking->status,
                'payment_method' => $booking->payment_method,
            ];
        });

        // Get recent reviews
        $recentReviews = $user->receivedReviews()
            ->with('student')
            ->latest()
            ->limit(5)
            ->get();

        $recentReviewsData = $recentReviews->map(function ($review) {
            return [
                'id' => $review->id,
                'student' => [
                    'full_name' => $review->student->full_name,
                ],
                'rating' => $review->rating,
                'comment' => $review->comment,
                'created_at' => $review->created_at->toISOString(),
                'date' => $review->created_at->format('Y-m-d'),
            ];
        });

        // Calculate stats - Direct from tutor model
        $thisMonth = now()->startOfMonth();
        $stats = [
            'total_earnings' => (float) $tutor->total_earnings,
            'total_lessons' => (int) $tutor->total_lessons,
            'average_rating' => (float) $tutor->rating,
            'total_reviews' => (int) $tutor->total_reviews,
            'this_month_bookings' => $user->tutorBookings()
                ->where('created_at', '>=', $thisMonth)
                ->count(),
            'pending_payments' => (float) $user->tutorBookings()
                ->where('payment_method', 'cash')
                ->where('payment_status', 'pending')
                ->where('status', 'completed')
                ->sum('price'),
        ];

        Log::info('Dashboard Debug - Final Stats:', $stats);

        // Get subscription
        $subscription = $user->subscription;
        $subscriptionData = null;

        if ($subscription) {
            $subscriptionData = [
                'plan_type' => $subscription->plan_type,
                'status' => $subscription->status,
                'expires_at' => $subscription->expires_at ? $subscription->expires_at->format('Y-m-d') : null,
                'trial_days_remaining' => $subscription->expires_at ?
                    max(0, $subscription->expires_at->diffInDays(now())) : 0,
                'features' => $this->getSubscriptionFeatures($subscription->plan_type),
            ];
        }

        // Get pending cash payments
        $pendingCashPayments = $user->tutorBookings()
            ->with('student')
            ->where('payment_method', 'cash')
            ->where('payment_status', 'pending')
            ->where('status', 'completed')
            ->get();

        $pendingCashPaymentsData = $pendingCashPayments->map(function ($booking) {
            return [
                'id' => $booking->id,
                'studentName' => $booking->student->full_name,
                'amount' => (float) $booking->price,
                'date' => $booking->completed_at ? $booking->completed_at->format('Y-m-d') : $booking->scheduled_at->format('Y-m-d'),
                'lessons' => 1,
            ];
        });

        // Tutor profile data
        $tutorData = [
            'bio' => $tutor->bio,
            'subjects' => $tutor->subjects->pluck('name')->toArray(),
            'hourly_rate' => (float) $tutor->hourly_rate,
            'availabilities' => $tutor->availabilities->isNotEmpty() ? ['Available'] : [],
            'photo' => $tutor->profile_image,
            'location' => $tutor->location ? $tutor->location->city : null,
            'experience' => $tutor->experience,
            'education' => $tutor->education,
        ];

        $response = [
            'stats' => $stats,
            'subscription' => $subscriptionData,
            'pending_bookings' => $pendingBookingsData,
            'upcoming_bookings' => $upcomingBookingsData,
            'recent_reviews' => $recentReviewsData,
            'pending_cash_payments' => $pendingCashPaymentsData,
            'tutor' => $tutorData,
            'notifications' => [],
            'weekly_stats' => [
                'lessons' => $user->tutorBookings()->whereBetween('scheduled_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'earnings' => (float) $user->tutorBookings()->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('price'),
                'newStudents' => $user->tutorBookings()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->distinct('student_id')->count(),
                'occupancyRate' => 85,
            ],
        ];

        Log::info('Dashboard Debug - Full Response:', $response);

        return response()->json($response);
    }

    private function getSubscriptionFeatures($planType)
    {
        switch ($planType) {
            case 'free_trial':
                return ['Up to 5 bookings per month', 'Basic profile', 'Email support'];
            case 'basic':
                return ['Unlimited bookings', 'Enhanced profile', 'Email support'];
            case 'premium':
                return ['Unlimited bookings', 'Featured profile', 'Priority support', 'Analytics'];
            default:
                return [];
        }
    }
}
