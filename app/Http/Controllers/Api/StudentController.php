<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StudentController extends Controller
{
    /**
     * Get student dashboard data (enhanced version with weekly stats)
     */
    public function getDashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isStudent()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $currentDate = Carbon::now();
        $weekStart = $currentDate->copy()->startOfWeek();
        $weekEnd = $currentDate->copy()->endOfWeek();
        $monthStart = $currentDate->copy()->startOfMonth();

        // Get upcoming bookings
        $upcomingBookings = $user->studentBookings()
            ->with(['tutor.tutor', 'subject'])
            ->where('scheduled_at', '>', $currentDate)
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'scheduled_at' => $booking->scheduled_at->toISOString(),
                    'duration_minutes' => $booking->duration_minutes,
                    'lesson_type' => $booking->lesson_type,
                    'price' => $booking->price,
                    'status' => $booking->status,
                    'payment_method' => $booking->payment_method,
                    'payment_status' => $booking->payment_status,
                    'student_notes' => $booking->student_notes,
                    'tutor' => [
                        'id' => $booking->tutor->id,
                        'first_name' => $booking->tutor->first_name,
                        'last_name' => $booking->tutor->last_name,
                        'profile_image' => $booking->tutor->tutor->profile_image_url ?? null,
                        'location' => $booking->tutor->tutor->location ? [
                            'city' => $booking->tutor->tutor->location->city,
                            'county' => $booking->tutor->tutor->location->county,
                        ] : null,
                    ],
                    'subject' => [
                        'id' => $booking->subject->id,
                        'name' => $booking->subject->name,
                        'icon' => $booking->subject->icon,
                    ],
                ];
            });

        // Get recent completed bookings
        $recentBookings = $user->studentBookings()
            ->with(['tutor.tutor', 'subject', 'review'])
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'scheduled_at' => $booking->scheduled_at->toISOString(),
                    'completed_at' => $booking->completed_at?->toISOString(),
                    'duration_minutes' => $booking->duration_minutes,
                    'lesson_type' => $booking->lesson_type,
                    'price' => $booking->price,
                    'status' => $booking->status,
                    'payment_status' => $booking->payment_status,
                    'can_review' => $booking->canBeReviewed(),
                    'tutor' => [
                        'id' => $booking->tutor->id,
                        'first_name' => $booking->tutor->first_name,
                        'last_name' => $booking->tutor->last_name,
                        'profile_image' => $booking->tutor->tutor->profile_image_url ?? null,
                    ],
                    'subject' => [
                        'id' => $booking->subject->id,
                        'name' => $booking->subject->name,
                        'icon' => $booking->subject->icon,
                    ],
                    'review' => $booking->review ? [
                        'id' => $booking->review->id,
                        'rating' => $booking->review->rating,
                        'comment' => $booking->review->comment,
                        'created_at' => $booking->review->created_at->toISOString(),
                    ] : null,
                ];
            });

        // Calculate comprehensive stats
        $stats = [
            'total_bookings' => $user->studentBookings()->count(),
            'completed_lessons' => $user->studentBookings()
                ->where('status', 'completed')->count(),
            'this_month_bookings' => $user->studentBookings()
                ->where('created_at', '>=', $monthStart)->count(),
            'this_week_bookings' => $user->studentBookings()
                ->where('created_at', '>=', $weekStart)->count(),
            'total_spent' => $user->studentBookings()
                ->where('payment_status', 'paid')->sum('price'),
            'this_month_spent' => $user->studentBookings()
                ->where('payment_status', 'paid')
                ->where('created_at', '>=', $monthStart)
                ->sum('price'),
            'pending_reviews' => $user->studentBookings()
                ->where('status', 'completed')
                ->whereDoesntHave('review')
                ->count(),
            'upcoming_lessons' => $user->studentBookings()
                ->where('scheduled_at', '>', $currentDate)
                ->whereIn('status', ['pending', 'confirmed'])
                ->count(),
            'favorite_subjects_count' => $user->studentBookings()
                ->distinct('subject_id')
                ->count(),
            'unique_tutors' => $user->studentBookings()
                ->distinct('tutor_id')
                ->count(),
        ];

        // Get favorite subjects with booking counts
        $favoriteSubjects = $user->studentBookings()
            ->with('subject')
            ->selectRaw('subject_id, COUNT(*) as booking_count')
            ->groupBy('subject_id')
            ->orderBy('booking_count', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($booking) {
                return [
                    'subject' => [
                        'id' => $booking->subject->id,
                        'name' => $booking->subject->name,
                        'icon' => $booking->subject->icon,
                    ],
                    'booking_count' => $booking->booking_count,
                ];
            });

        // Enhanced Weekly stats
        $weeklyStats = [
            'lessons' => $user->studentBookings()
                ->where('scheduled_at', '>=', $weekStart)
                ->where('scheduled_at', '<=', $weekEnd)
                ->whereIn('status', ['confirmed', 'completed'])
                ->count(),
            'spent' => $user->studentBookings()
                ->where('scheduled_at', '>=', $weekStart)
                ->where('scheduled_at', '<=', $weekEnd)
                ->where('status', 'completed')
                ->where('payment_status', 'paid')
                ->sum('price'),
            'new_tutors' => $user->studentBookings()
                ->where('created_at', '>=', $weekStart)
                ->where('created_at', '<=', $weekEnd)
                ->distinct('tutor_id')
                ->count(),
            'subjects_studied' => $user->studentBookings()
                ->where('scheduled_at', '>=', $weekStart)
                ->where('scheduled_at', '<=', $weekEnd)
                ->whereIn('status', ['confirmed', 'completed'])
                ->distinct('subject_id')
                ->count(),
        ];

        // Recent activity data (last 7 days)
        $recentActivityData = $this->generateRecentActivity($user, 7);

        return response()->json([
            'stats' => $stats,
            'upcoming_bookings' => $upcomingBookings,
            'recent_bookings' => $recentBookings,
            'favorite_subjects' => $favoriteSubjects,
            'weekly_stats' => $weeklyStats,
            'recent_activity' => $recentActivityData,
        ]);
    }

    /**
     * Generate recent activity for the student
     */
    private function generateRecentActivity($user, $days = 7)
    {
        $since = Carbon::now()->subDays($days);
        $activities = [];

        // Get recent completed bookings
        $completedBookings = $user->studentBookings()
            ->with(['tutor.tutor', 'subject', 'review'])
            ->where('completed_at', '>=', $since)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->get();

        foreach ($completedBookings as $booking) {
            $activities[] = [
                'id' => 'completed-' . $booking->id,
                'type' => 'booking_completed',
                'title' => 'Lecție finalizată',
                'description' => $booking->subject->name . ' cu ' . $booking->tutor->first_name . ' ' . $booking->tutor->last_name,
                'created_at' => $booking->completed_at->toISOString(),
                'booking_id' => $booking->id,
            ];

            // Add review activity if exists
            if ($booking->review && $booking->review->created_at >= $since) {
                $activities[] = [
                    'id' => 'review-' . $booking->id,
                    'type' => 'review_submitted',
                    'title' => 'Review trimis',
                    'description' => 'Ai evaluat lecția de ' . $booking->subject->name . ' cu ' . $booking->review->rating . ' stele',
                    'created_at' => $booking->review->created_at->toISOString(),
                    'booking_id' => $booking->id,
                ];
            }
        }

        // Get recent created bookings
        $newBookings = $user->studentBookings()
            ->with(['tutor.tutor', 'subject'])
            ->where('created_at', '>=', $since)
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($newBookings as $booking) {
            $activities[] = [
                'id' => 'created-' . $booking->id,
                'type' => 'booking_created',
                'title' => 'Lecție programată',
                'description' => $booking->subject->name . ' cu ' . $booking->tutor->first_name . ' ' . $booking->tutor->last_name,
                'created_at' => $booking->created_at->toISOString(),
                'booking_id' => $booking->id,
            ];
        }

        // Get cancelled bookings
        $cancelledBookings = $user->studentBookings()
            ->with(['tutor.tutor', 'subject'])
            ->where('cancelled_at', '>=', $since)
            ->where('status', 'cancelled')
            ->orderBy('cancelled_at', 'desc')
            ->get();

        foreach ($cancelledBookings as $booking) {
            $activities[] = [
                'id' => 'cancelled-' . $booking->id,
                'type' => 'booking_cancelled',
                'title' => 'Lecție anulată',
                'description' => $booking->subject->name . ' cu ' . $booking->tutor->first_name . ' ' . $booking->tutor->last_name,
                'created_at' => $booking->cancelled_at->toISOString(),
                'booking_id' => $booking->id,
            ];
        }

        // Sort by creation date and limit to 10 most recent
        usort($activities, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return array_slice($activities, 0, 10);
    }

    /**
     * Get student bookings with filters
     */
    public function getBookings(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isStudent()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = $user->studentBookings()
            ->with(['tutor.tutor', 'subject', 'review']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('tutor_id')) {
            $query->where('tutor_id', $request->tutor_id);
        }

        if ($request->filled('date_from')) {
            $query->where('scheduled_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('scheduled_at', '<=', $request->date_to);
        }

        $bookings = $query->orderBy('scheduled_at', 'desc')
            ->paginate($request->get('per_page', 10));

        return response()->json(['bookings' => $bookings]);
    }

    /**
     * Get student profile
     */
    public function getProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isStudent()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'user_type' => $user->user_type,
                'created_at' => $user->created_at->toISOString(),
            ],
        ]);
    }

    /**
     * Update student profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isStudent()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'phone' => 'nullable|string|regex:/^(\+40|0)[0-9]{9}$/',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'user_type' => $user->user_type,
            ],
        ]);
    }
}
