<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tutor;
use App\Models\User;
use App\Models\Subject;
use App\Models\Location;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Availability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class TutorController extends Controller
{
    /**
     * Display a listing of tutors (public endpoint)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tutor::with(['user', 'location', 'subjects', 'reviews'])
            ->where('is_active', true);

        // Filter by subject
        if ($request->filled('subject')) {
            $query->whereHas('subjects', function ($q) use ($request) {
                $q->where('subjects.slug', $request->subject)
                  ->orWhere('subjects.id', $request->subject);
            });
        }

        // Filter by location
        if ($request->filled('location')) {
            $query->whereHas('location', function ($q) use ($request) {
                $q->where('locations.slug', $request->location)
                  ->orWhere('locations.id', $request->location);
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
        if ($request->filled('min_price')) {
            $query->where('hourly_rate', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('hourly_rate', '<=', $request->max_price);
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

        // Sorting
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
            case 'experience':
                $query->orderBy('total_lessons', 'desc');
                break;
            default:
                $query->orderBy('is_featured', 'desc')
                      ->orderBy('rating', 'desc');
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

    /**
     * Display the specified tutor (public endpoint)
     */
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

    /**
     * Get tutor dashboard data
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isTutor()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $tutor = $user->tutor;

        // Get pending bookings
        $pendingBookings = $user->tutorBookings()
            ->with(['student', 'subject'])
            ->where('status', 'pending')
            ->orderBy('scheduled_at')
            ->limit(10)
            ->get();

        // Get upcoming bookings
        $upcomingBookings = $user->tutorBookings()
            ->with(['student', 'subject'])
            ->where('scheduled_at', '>', now())
            ->whereIn('status', ['confirmed'])
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get();

        // Get recent reviews
        $recentReviews = $user->receivedReviews()
            ->with('student')
            ->latest()
            ->limit(5)
            ->get();

        // Get pending cash payments
        $pendingCashPayments = $user->tutorBookings()
            ->with(['student'])
            ->where('payment_method', 'cash')
            ->where('payment_status', 'pending')
            ->where('status', 'completed')
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

        // Weekly stats
        $weekStart = now()->startOfWeek();
        $weeklyStats = [
            'lessons' => $user->tutorBookings()
                ->where('scheduled_at', '>=', $weekStart)
                ->where('scheduled_at', '<', $weekStart->copy()->addWeek())
                ->whereIn('status', ['confirmed', 'completed'])
                ->count(),
            'earnings' => $user->tutorBookings()
                ->where('scheduled_at', '>=', $weekStart)
                ->where('scheduled_at', '<', $weekStart->copy()->addWeek())
                ->where('status', 'completed')
                ->where('payment_status', 'paid')
                ->sum('price'),
            'newStudents' => $user->tutorBookings()
                ->where('created_at', '>=', $weekStart)
                ->distinct('student_id')
                ->count(),
            'occupancyRate' => 85, // This would need more complex calculation
        ];

        // Get notifications (mock for now)
        $notifications = [];

        return response()->json([
            'stats' => $stats,
            'pending_bookings' => $pendingBookings,
            'upcoming_bookings' => $upcomingBookings,
            'recent_reviews' => $recentReviews,
            'pending_cash_payments' => $pendingCashPayments,
            'notifications' => $notifications,
            'subscription' => $user->subscription,
            'tutor' => $tutor->load(['subjects', 'availabilities']),
            'weekly_stats' => $weeklyStats,
        ]);
    }

    /**
     * Get tutor profile for editing
     */
    public function getProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isTutor()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $tutor = $user->tutor()->with(['user', 'location', 'subjects'])->first();

        if (!$tutor) {
            return response()->json(['message' => 'Tutor profile not found'], 404);
        }

        return response()->json([
            'tutor' => [
                'user' => $user,
                'bio' => $tutor->bio,
                'experience' => $tutor->experience,
                'education' => $tutor->education,
                'hourly_rate' => $tutor->hourly_rate,
                'location_id' => $tutor->location_id,
                'location' => $tutor->location,
                'offers_online' => $tutor->offers_online,
                'offers_in_person' => $tutor->offers_in_person,
                'profile_image_url' => $tutor->profile_image_url,
                'subjects' => $tutor->subjects,
                'rating' => $tutor->rating,
                'total_reviews' => $tutor->total_reviews,
                'total_lessons' => $tutor->total_lessons,
                'is_verified' => $tutor->is_verified,
            ]
        ]);
    }

    /**
     * Update tutor profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isTutor()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Debug the request to see what's being sent
        \Log::info('Profile update request', [
            'files' => $request->allFiles(),
            'has_profile_image' => $request->hasFile('profile_image'),
            'profile_image_info' => $request->file('profile_image') ? [
                'original_name' => $request->file('profile_image')->getClientOriginalName(),
                'mime_type' => $request->file('profile_image')->getMimeType(),
                'size' => $request->file('profile_image')->getSize(),
            ] : null
        ]);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:20'],
            'bio' => ['required', 'string', 'max:1000'],
            'experience' => ['required', 'string', 'max:2000'],
            'education' => ['required', 'string', 'max:2000'],
            'hourly_rate' => ['required', 'numeric', 'min:20', 'max:500'],
            'location_id' => ['required', 'exists:locations,id'],
            'offers_online' => ['required', 'in:true,false,1,0'],
            'offers_in_person' => ['required', 'in:true,false,1,0'],
            'subjects' => ['required', 'array', 'min:1'],
            'subjects.*' => ['exists:subjects,id'],
            'profile_image' => ['nullable', 'file', 'mimes:jpeg,jpg,png,gif', 'max:2048'],
        ]);

        // Convert string boolean values to actual booleans
        $validated['offers_online'] = in_array($validated['offers_online'], ['true', '1', 1, true]);
        $validated['offers_in_person'] = in_array($validated['offers_in_person'], ['true', '1', 1, true]);

        // Update user information
        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'],
        ]);

        $tutor = $user->tutor;

        // Handle profile image upload
        $profileImagePath = $tutor->profile_image;
        if ($request->hasFile('profile_image') && $request->file('profile_image')->isValid()) {
            try {
                // Delete old image if exists
                if ($tutor->profile_image) {
                    Storage::delete('public/tutors/' . $tutor->profile_image);
                }

                $file = $request->file('profile_image');
                $filename = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();

                // Store the file
                $path = $file->storeAs('public/tutors', $filename);

                if ($path) {
                    $profileImagePath = $filename;
                    \Log::info('Profile image uploaded successfully', ['filename' => $filename]);
                }
            } catch (\Exception $e) {
                \Log::error('Error uploading profile image', ['error' => $e->getMessage()]);
                return response()->json([
                    'message' => 'Error uploading profile image: ' . $e->getMessage()
                ], 422);
            }
        }

        // Update tutor information
        $tutor->update([
            'bio' => $validated['bio'],
            'experience' => $validated['experience'],
            'education' => $validated['education'],
            'hourly_rate' => $validated['hourly_rate'],
            'location_id' => $validated['location_id'],
            'offers_online' => $validated['offers_online'],
            'offers_in_person' => $validated['offers_in_person'],
            'profile_image' => $profileImagePath,
        ]);

        // Update subjects
        $tutor->subjects()->sync($validated['subjects']);

        // Reload tutor with relationships
        $tutor->load(['user', 'location', 'subjects']);

        return response()->json([
            'message' => 'Profile updated successfully',
            'tutor' => [
                'user' => $user->fresh(),
                'bio' => $tutor->bio,
                'experience' => $tutor->experience,
                'education' => $tutor->education,
                'hourly_rate' => $tutor->hourly_rate,
                'location_id' => $tutor->location_id,
                'location' => $tutor->location,
                'offers_online' => $tutor->offers_online,
                'offers_in_person' => $tutor->offers_in_person,
                'profile_image_url' => $tutor->profile_image_url,
                'subjects' => $tutor->subjects,
                'rating' => $tutor->rating,
                'total_reviews' => $tutor->total_reviews,
                'total_lessons' => $tutor->total_lessons,
                'is_verified' => $tutor->is_verified,
            ]
        ]);
    }

    /**
     * Get tutor availability
     */
    public function getAvailability(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isTutor()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $availability = $user->tutor->availabilities()
            ->where('is_active', true)
            ->orderByRaw("FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->orderBy('start_time')
            ->get();

        // Group by day of week and convert to the format expected by frontend
        $groupedAvailability = $availability->groupBy('day_of_week');

        // Convert day names to numbers for frontend compatibility
        $dayMapping = [
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            'sunday' => 0
        ];

        $convertedAvailability = [];
        foreach ($groupedAvailability as $dayName => $slots) {
            $dayNumber = $dayMapping[$dayName];
            $convertedAvailability[$dayNumber] = $slots->map(function ($slot) {
                return [
                    'id' => $slot->id,
                    'start_time' => $slot->formatted_start_time,
                    'end_time' => $slot->formatted_end_time,
                    'lesson_type' => $slot->lesson_type,
                    'is_active' => $slot->is_active,
                ];
            });
        }

        return response()->json([
            'availability' => $convertedAvailability
        ]);
    }

    /**
     * Update tutor availability
     */
    public function updateAvailability(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isTutor()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'availability' => ['required', 'array'],
            'availability.*.day_of_week' => ['required', 'integer', 'min:0', 'max:6'],
            'availability.*.start_time' => ['required', 'date_format:H:i'],
            'availability.*.end_time' => ['required', 'date_format:H:i', 'after:availability.*.start_time'],
            'availability.*.lesson_type' => ['required', 'in:online,in_person,both'],
            'availability.*.is_active' => ['boolean']
        ]);

        $tutor = $user->tutor;

        // Convert day numbers back to day names
        $dayMapping = [
            0 => 'sunday',
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday'
        ];

        // Delete existing availability
        $tutor->availabilities()->delete();

        // Create new availability slots
        foreach ($validated['availability'] as $slot) {
            $dayName = $dayMapping[$slot['day_of_week']];

            $tutor->availabilities()->create([
                'day_of_week' => $dayName,
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
                'lesson_type' => $slot['lesson_type'],
                'is_active' => $slot['is_active'] ?? true,
            ]);
        }

        // Return updated availability
        $availability = $tutor->availabilities()
            ->where('is_active', true)
            ->orderByRaw("FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->orderBy('start_time')
            ->get();

        $groupedAvailability = $availability->groupBy('day_of_week');
        $convertedAvailability = [];

        $reverseDayMapping = [
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            'sunday' => 0
        ];

        foreach ($groupedAvailability as $dayName => $slots) {
            $dayNumber = $reverseDayMapping[$dayName];
            $convertedAvailability[$dayNumber] = $slots->map(function ($slot) {
                return [
                    'id' => $slot->id,
                    'start_time' => $slot->formatted_start_time,
                    'end_time' => $slot->formatted_end_time,
                    'lesson_type' => $slot->lesson_type,
                    'is_active' => $slot->is_active,
                ];
            });
        }

        return response()->json([
            'message' => 'Availability updated successfully',
            'availability' => $convertedAvailability
        ]);
    }

    /**
     * Get tutor reviews
     */
    public function getReviews(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isTutor()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $perPage = min($request->get('per_page', 15), 50);

        $reviews = $user->receivedReviews()
            ->with(['student', 'booking.subject'])
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'reviews' => $reviews->items(),
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    /**
     * Get tutor earnings and statistics
     */
    public function getEarnings(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isTutor()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $tutor = $user->tutor;

        // Get date range (default to current month)
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        // Calculate earnings for the period
        $completedBookings = $user->tutorBookings()
            ->where('status', 'completed')
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->with(['student', 'subject'])
            ->get();

        $totalEarnings = $completedBookings->sum('price');
        $paidEarnings = $completedBookings->where('payment_status', 'paid')->sum('price');
        $pendingEarnings = $completedBookings->where('payment_status', 'pending')->sum('price');

        // Group by month for chart data
        $monthlyEarnings = $completedBookings
            ->where('payment_status', 'paid')
            ->groupBy(function ($booking) {
                return Carbon::parse($booking->scheduled_at)->format('Y-m');
            })
            ->map(function ($bookings) {
                return $bookings->sum('price');
            });

        return response()->json([
            'earnings' => [
                'total' => $totalEarnings,
                'paid' => $paidEarnings,
                'pending' => $pendingEarnings,
                'total_lifetime' => $tutor->total_earnings,
            ],
            'bookings' => $completedBookings,
            'monthly_earnings' => $monthlyEarnings,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }
}
