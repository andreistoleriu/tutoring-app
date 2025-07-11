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
/**
 * Display the specified tutor (public endpoint)
 */
public function show($id): JsonResponse
{
    try {
        // Debug: Log the incoming ID
        \Log::info('Fetching tutor with ID: ' . $id);

        // First, let's check if we're looking for tutor by user_id or tutor.id
        // The route parameter might be the user ID, not the tutor table ID

        $tutor = Tutor::with([
            'user',
            'location',
            'subjects',
            'reviews.student',
            'availabilities' => function ($q) {
                $q->where('is_active', true)->orderBy('day_of_week');
            }
        ])
        ->where('user_id', $id) // Try user_id first
        ->where('is_active', true)
        ->first();

        // If not found by user_id, try by tutor.id
        if (!$tutor) {
            $tutor = Tutor::with([
                'user',
                'location',
                'subjects',
                'reviews.student',
                'availabilities' => function ($q) {
                    $q->where('is_active', true)->orderBy('day_of_week');
                }
            ])
            ->where('id', $id)
            ->where('is_active', true)
            ->first();
        }

        if (!$tutor) {
            \Log::warning('Tutor not found with ID: ' . $id);
            return response()->json([
                'message' => 'Tutor not found'
            ], 404);
        }

        // Debug: Log successful find
        \Log::info('Tutor found: ' . $tutor->user->first_name . ' ' . $tutor->user->last_name);

        // Update last active timestamp
        $tutor->update(['last_active' => now()]);

        // Format the response data safely
        $tutorData = [
            'id' => $tutor->id,
            'user_id' => $tutor->user_id, // Add this for booking
            'user' => [
                'id' => $tutor->user->id,
                'first_name' => $tutor->user->first_name,
                'last_name' => $tutor->user->last_name,
                'full_name' => $tutor->user->first_name . ' ' . $tutor->user->last_name,
            ],
            'bio' => $tutor->bio,
            'profile_image_url' => $tutor->profile_image_url,
            'hourly_rate' => (float) $tutor->hourly_rate,
            'offers_online' => (bool) $tutor->offers_online,
            'offers_in_person' => (bool) $tutor->offers_in_person,
            'experience' => $tutor->experience,
            'education' => $tutor->education,
            'rating' => round((float) $tutor->rating, 1),
            'total_reviews' => (int) $tutor->total_reviews,
            'total_lessons' => (int) $tutor->total_lessons,
            'is_verified' => (bool) $tutor->is_verified,
            'is_featured' => (bool) $tutor->is_featured,
            'last_active' => $tutor->last_active?->diffForHumans(),
            'location' => $tutor->location ? [
                'id' => $tutor->location->id,
                'city' => $tutor->location->city,
                'county' => $tutor->location->county,
                'full_name' => $tutor->location->city . ', ' . $tutor->location->county,
            ] : null,
            'subjects' => $tutor->subjects->map(function ($subject) {
                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'slug' => $subject->slug,
                    'description' => $subject->description,
                    'pivot' => [
                        'experience_description' => $subject->pivot->experience_description ?? null,
                    ],
                ];
            }),
            'reviews' => $tutor->reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'rating' => (int) $review->rating,
                    'comment' => $review->comment,
                    'tutor_reply' => $review->tutor_reply,
                    'student' => [
                        'id' => $review->student->id,
                        'first_name' => $review->student->first_name,
                        'last_name' => $review->student->last_name,
                    ],
                    'created_at' => $review->created_at->toISOString(),
                ];
            }),
            'availabilities' => $tutor->availabilities->map(function ($availability) {
                return [
                    'id' => $availability->id,
                    'day_of_week' => $availability->day_of_week,
                    'start_time' => $availability->start_time,
                    'end_time' => $availability->end_time,
                    'lesson_type' => $availability->lesson_type,
                    'is_active' => (bool) $availability->is_active,
                ];
            }),
        ];

        return response()->json([
            'tutor' => $tutorData
        ]);

    } catch (\Exception $e) {
        \Log::error('Error fetching tutor: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());

        return response()->json([
            'message' => 'Error loading tutor profile',
            'error' => $e->getMessage()
        ], 500);
    }
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
     * Get tutor dashboard data
     */
    /**
     * Get tutor dashboard data
     */
    public function getDashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isTutor()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $tutor = $user->tutor;
        $currentDate = Carbon::now();
        $weekStart = $currentDate->copy()->startOfWeek();
        $weekEnd = $currentDate->copy()->endOfWeek();

        // Get stats
        $stats = [
            'total_earnings' => $tutor->bookings()
                ->where('status', 'completed')
                ->where('payment_status', 'paid')
                ->sum('price'),
            'total_lessons' => $tutor->bookings()
                ->where('status', 'completed')
                ->count(),
            'total_students' => $tutor->bookings()
                ->distinct('student_id')
                ->count(),
            'average_rating' => $tutor->reviews()->avg('rating') ?: 0,
            'this_week_earnings' => $tutor->bookings()
                ->where('status', 'completed')
                ->where('payment_status', 'paid')
                ->whereBetween('scheduled_at', [$weekStart, $weekEnd])
                ->sum('price'),
            'this_week_lessons' => $tutor->bookings()
                ->where('status', 'completed')
                ->whereBetween('scheduled_at', [$weekStart, $weekEnd])
                ->count(),
        ];

        // Get pending bookings
        $pendingBookings = $tutor->bookings()
            ->with(['student', 'subject'])
            ->where('status', 'pending')
            ->orderBy('scheduled_at', 'asc')
            ->get()
            ->map(function ($booking) {
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
                    'price' => $booking->price,
                    'status' => $booking->status,
                    'payment_method' => $booking->payment_method,
                    'payment_status' => $booking->payment_status,
                    'student_notes' => $booking->student_notes,
                ];
            });

        // Get upcoming bookings
        $upcomingBookings = $tutor->bookings()
            ->with(['student', 'subject'])
            ->where('status', 'confirmed')
            ->where('scheduled_at', '>', $currentDate)
            ->orderBy('scheduled_at', 'asc')
            ->limit(5)
            ->get()
            ->map(function ($booking) {
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
                    'price' => $booking->price,
                    'status' => $booking->status,
                    'payment_method' => $booking->payment_method,
                    'payment_status' => $booking->payment_status,
                ];
            });

        // Get recent reviews
        $recentReviews = $tutor->reviews()
            ->with(['student', 'booking.subject'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'student' => [
                        'full_name' => $review->student->full_name,
                    ],
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'subject' => $review->booking->subject->name,
                    'created_at' => $review->created_at->toISOString(),
                ];
            });

        // Get pending cash payments
        $pendingCashPayments = $tutor->bookings()
            ->with(['student'])
            ->where('payment_method', 'cash')
            ->where('payment_status', 'pending')
            ->where('status', 'completed')
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'student_name' => $booking->student->full_name,
                    'amount' => $booking->price,
                    'date' => $booking->scheduled_at->toDateString(),
                    'subject' => $booking->subject->name,
                ];
            });

        // Get week schedule using the helper method
        $weekSchedule = $this->buildWeekSchedule($tutor, $weekStart, $weekEnd);

        return response()->json([
            'stats' => $stats,
            'tutor' => [
                'id' => $tutor->id,
                'bio' => $tutor->bio,
                'hourly_rate' => $tutor->hourly_rate,
                'subjects' => $tutor->subjects->pluck('name'),
                'availabilities' => $tutor->availabilities()->where('is_active', true)->get(),
                'photo' => $tutor->profile_image_url,
                'location' => $tutor->location ? $tutor->location->city : null,
                'experience' => $tutor->experience,
                'education' => $tutor->education,
            ],
            'pending_bookings' => $pendingBookings,
            'upcoming_bookings' => $upcomingBookings,
            'recent_reviews' => $recentReviews,
            'pending_cash_payments' => $pendingCashPayments,
            'week_schedule' => $weekSchedule,
            'subscription' => [
                'plan' => 'free_trial', // You'll need to implement subscription logic
                'expires_at' => null,
            ],
        ]);
    }



public function getWeekSchedule(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isTutor()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $tutor = $user->tutor;

        // Get week start from request or default to current week
        $weekStart = $request->has('week_start')
            ? Carbon::parse($request->week_start)->startOfWeek()
            : Carbon::now()->startOfWeek();

        $weekEnd = $weekStart->copy()->endOfWeek();

        $schedule = $this->buildWeekSchedule($tutor, $weekStart, $weekEnd);

        return response()->json([
            'week_start' => $weekStart->toDateString(),
            'week_end' => $weekEnd->toDateString(),
            'schedule' => $schedule,
        ]);
    }

    /**
     * Helper method to build week schedule
     * (renamed from getWeekSchedule to avoid conflict)
     */
    private function buildWeekSchedule($tutor, $weekStart, $weekEnd): array
    {
        $dayNames = ['Luni', 'Marți', 'Miercuri', 'Joi', 'Vineri', 'Sâmbătă', 'Duminică'];
        $schedule = [];

        for ($i = 0; $i < 7; $i++) {
            $currentDay = $weekStart->copy()->addDays($i);
            $dayName = $dayNames[$i];
            $englishDayName = $currentDay->format('l'); // Monday, Tuesday, etc.

            // Get bookings for this day
            $bookings = $tutor->bookings()
                ->with(['student', 'subject'])
                ->whereDate('scheduled_at', $currentDay)
                ->where('status', '!=', 'cancelled')
                ->orderBy('scheduled_at', 'asc')
                ->get()
                ->map(function ($booking) {
                    return [
                        'id' => $booking->id,
                        'student' => $booking->student->full_name,
                        'subject' => $booking->subject->name,
                        'time' => $booking->scheduled_at->format('H:i'),
                        'duration' => $booking->duration_minutes ?? 60,
                        'type' => $booking->lesson_type,
                        'status' => $booking->status,
                        'payment_method' => $booking->payment_method,
                        'payment_status' => $booking->payment_status,
                        'scheduled_at' => $booking->scheduled_at->toISOString(),
                    ];
                });

            // Get available slots for this day
            $availabilities = $tutor->availabilities()
                ->where('day_of_week', strtolower($englishDayName))
                ->where('is_active', true)
                ->get();

            $availableSlots = $availabilities->map(function ($availability) {
                return $availability->start_time . ' - ' . $availability->end_time;
            });

            $schedule[] = [
                'date' => $currentDay->toDateString(),
                'day_name' => $dayName,
                'bookings' => $bookings,
                'available_slots' => $availableSlots,
                'has_lessons' => $bookings->count() > 0,
            ];
        }

        return $schedule;
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
            $page = $request->get('page', 1);

            // Get reviews for this tutor through their bookings
            $reviews = Review::query()
                ->whereHas('booking', function ($query) use ($user) {
                    $query->where('tutor_id', $user->id);
                })
                ->with(['student', 'booking.subject'])
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page);

            // Format the reviews data
            $formattedReviews = $reviews->getCollection()->map(function ($review) {
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'student' => [
                        'id' => $review->student->id,
                        'first_name' => $review->student->first_name,
                        'last_name' => $review->student->last_name,
                        'full_name' => $review->student->first_name . ' ' . $review->student->last_name,
                        'email' => $review->student->email,
                    ],
                    'subject' => [
                        'id' => $review->booking->subject->id,
                        'name' => $review->booking->subject->name,
                    ],
                    'booking_id' => $review->booking_id,
                    'created_at' => $review->created_at->toISOString(),
                    'reply' => $review->tutor_reply,
                    'reply_date' => $review->tutor_reply_at ? $review->tutor_reply_at->toISOString() : null,
                ];
            });

            return response()->json([
                'reviews' => $formattedReviews,
                'pagination' => [
                    'current_page' => $reviews->currentPage(),
                    'last_page' => $reviews->lastPage(),
                    'per_page' => $reviews->perPage(),
                    'total' => $reviews->total(),
                    'from' => $reviews->firstItem(),
                    'to' => $reviews->lastItem(),
                ],
            ]);
    }

   /**
 * Get public tutor availability (for students to view when booking)
 */
public function getPublicAvailability(Request $request, $id): JsonResponse
{
    try {
        // Find tutor by user_id first, then by tutor.id
        $tutor = Tutor::where('user_id', $id)
            ->where('is_active', true)
            ->first();

        if (!$tutor) {
            $tutor = Tutor::where('id', $id)
                ->where('is_active', true)
                ->first();
        }

        if (!$tutor) {
            return response()->json([
                'message' => 'Tutor not found or inactive'
            ], 404);
        }

        $availability = $tutor->availabilities()
            ->where('is_active', true)
            ->orderByRaw("FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->orderBy('start_time')
            ->get();

        // Group by day of week
        $groupedAvailability = $availability->groupBy('day_of_week');

        // Convert to format expected by frontend
        $formattedAvailability = [];
        foreach ($groupedAvailability as $dayName => $slots) {
            $formattedAvailability[$dayName] = $slots->map(function ($slot) {
                return [
                    'start_time' => $slot->start_time,
                    'end_time' => $slot->end_time,
                    'lesson_type' => $slot->lesson_type,
                ];
            })->toArray();
        }

        return response()->json([
            'availability' => $formattedAvailability
        ]);

    } catch (\Exception $e) {
        \Log::error('Error in getPublicAvailability:', [
            'tutor_id' => $id,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'message' => 'Error loading availability',
            'availability' => []
        ], 500);
    }
}

/**
 * Get tutor's booked time slots for the next 14 days
 */
public function getBusySlots(Request $request, $id): JsonResponse
{
    try {
        $tutor = Tutor::where('user_id', $id)
            ->where('is_active', true)
            ->first();

        if (!$tutor) {
            $tutor = Tutor::where('id', $id)
                ->where('is_active', true)
                ->first();
        }

        if (!$tutor) {
            return response()->json([
                'message' => 'Tutor not found'
            ], 404);
        }

        $startDate = Carbon::now()->startOfDay();
        $endDate = Carbon::now()->addDays(14)->endOfDay();

        $busySlots = $tutor->user->tutorBookings()
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->whereIn('status', ['pending', 'confirmed'])
            ->select('scheduled_at', 'duration_minutes')
            ->get()
            ->map(function ($booking) {
                $start = $booking->scheduled_at;
                $end = $start->copy()->addMinutes($booking->duration_minutes);

                return [
                    'start' => $start->toISOString(),
                    'end' => $end->toISOString(),
                ];
            });

        return response()->json([
            'busy_slots' => $busySlots
        ]);

    } catch (\Exception $e) {
        \Log::error('Error in getBusySlots:', [
            'tutor_id' => $id,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'busy_slots' => []
        ], 500);
    }
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
