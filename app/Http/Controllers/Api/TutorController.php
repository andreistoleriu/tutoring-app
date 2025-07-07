<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tutor;
use App\Models\Subject;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TutorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Tutor::query()
            ->with(['user', 'location', 'subjects'])
            ->whereHas('user', function ($q) {
                $q->where('is_active', true);
            });

        // Search by name or bio
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            })->orWhere('bio', 'like', "%{$search}%");
        }

        // Filter by subject
        if ($request->filled('subject')) {
            $query->whereHas('subjects', function ($q) use ($request) {
                $q->where('subjects.slug', $request->subject);
            });
        }

        // Filter by location
        if ($request->filled('location')) {
            $query->whereHas('location', function ($q) use ($request) {
                $q->where('county', 'like', '%' . $request->location . '%')
                  ->orWhere('city', 'like', '%' . $request->location . '%');
            });
        }

        // Filter by lesson type
        if ($request->filled('lesson_type')) {
            $lessonType = $request->lesson_type;
            if ($lessonType === 'online') {
                $query->where('offers_online', true);
            } elseif ($lessonType === 'in_person') {
                $query->where('offers_in_person', true);
            }
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('hourly_rate', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('hourly_rate', '<=', $request->max_price);
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'created_at');
        switch ($sortBy) {
            case 'rating':
                $query->orderBy('rating', 'desc');
                break;
            case 'price_low':
                $query->orderBy('hourly_rate', 'asc');
                break;
            case 'price_high':
                $query->orderBy('hourly_rate', 'desc');
                break;
            case 'popular':
                $query->orderBy('total_lessons', 'desc');
                break;
            case 'featured':
                $query->orderBy('is_featured', 'desc')
                      ->orderBy('rating', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
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

        // Get upcoming bookings
        $upcomingBookings = $user->tutorBookings()
            ->with(['student', 'subject'])
            ->where('scheduled_at', '>', now())
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get();

        // Get recent reviews
        $recentReviews = $user->receivedReviews()
            ->with('student')
            ->latest()
            ->limit(5)
            ->get();

        // Calculate stats
        $thisMonth = now()->startOfMonth();
        $stats = [
            'total_earnings' => $tutor->total_earnings,
            'total_lessons' => $tutor->total_lessons,
            'average_rating' => $tutor->rating,
            'total_reviews' => $tutor->total_reviews,
            'this_month_bookings' => $user->tutorBookings()
                ->where('created_at', '>=', $thisMonth)
                ->count(),
            'pending_payments' => $user->tutorBookings()
                ->where('payment_method', 'cash')
                ->where('payment_status', 'pending')
                ->where('status', 'completed')
                ->sum('price'),
        ];

        return response()->json([
            'stats' => $stats,
            'upcoming_bookings' => $upcomingBookings,
            'recent_reviews' => $recentReviews,
            'subscription' => $user->subscription,
        ]);
    }
}
