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
    /**
     * Get student dashboard data
     */
    public function getDashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isStudent()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $currentDate = Carbon::now();

        // Get upcoming bookings (next 7 days)
        $upcomingBookings = $user->studentBookings()
            ->with(['tutor', 'subject'])
            ->where('scheduled_at', '>', $currentDate)
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('scheduled_at', 'asc')
            ->take(5)
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'tutor' => [
                        'id' => $booking->tutor->id,
                        'first_name' => $booking->tutor->first_name,
                        'last_name' => $booking->tutor->last_name,
                    ],
                    'subject' => [
                        'id' => $booking->subject->id,
                        'name' => $booking->subject->name,
                    ],
                    'scheduled_at' => $booking->scheduled_at->toISOString(),
                    'duration_minutes' => $booking->duration_minutes,
                    'lesson_type' => $booking->lesson_type,
                    'status' => $booking->status,
                    'price' => $booking->price,
                ];
            });

        // Get recent completed bookings that can be reviewed
        $pendingReviews = $user->studentBookings()
            ->with(['tutor', 'subject'])
            ->where('status', 'completed')
            ->whereDoesntHave('review')
            ->orderBy('completed_at', 'desc')
            ->take(3)
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'tutor' => [
                        'id' => $booking->tutor->id,
                        'first_name' => $booking->tutor->first_name,
                        'last_name' => $booking->tutor->last_name,
                    ],
                    'subject' => [
                        'id' => $booking->subject->id,
                        'name' => $booking->subject->name,
                    ],
                    'completed_at' => $booking->completed_at->toISOString(),
                    'lesson_type' => $booking->lesson_type,
                    'price' => $booking->price,
                ];
            });

        // Calculate stats
        $stats = [
            'total_bookings' => $user->studentBookings()->count(),
            'completed_lessons' => $user->studentBookings()->where('status', 'completed')->count(),
            'total_spent' => $user->studentBookings()
                ->where('status', 'completed')
                ->where('payment_status', 'paid')
                ->sum('price'),
            'pending_bookings' => $user->studentBookings()->where('status', 'pending')->count(),
        ];

        return response()->json([
            'stats' => $stats,
            'upcoming_bookings' => $upcomingBookings,
            'pending_reviews' => $pendingReviews,
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
            ->with(['tutor', 'subject', 'review']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_at', '<=', $request->date_to);
        }

        // Filter by type
        switch ($request->get('type')) {
            case 'upcoming':
                $query->where('scheduled_at', '>', now())
                      ->whereIn('status', ['pending', 'confirmed']);
                break;
            case 'past':
                $query->where('scheduled_at', '<', now());
                break;
            case 'completed':
                $query->where('status', 'completed');
                break;
        }

        $bookings = $query->orderBy('scheduled_at', 'desc')
            ->paginate($request->get('per_page', 15));

        // Transform the data
        $bookings->getCollection()->transform(function ($booking) {
            return [
                'id' => $booking->id,
                'tutor' => [
                    'id' => $booking->tutor->id,
                    'first_name' => $booking->tutor->first_name,
                    'last_name' => $booking->tutor->last_name,
                    'email' => $booking->tutor->email,
                ],
                'subject' => [
                    'id' => $booking->subject->id,
                    'name' => $booking->subject->name,
                ],
                'scheduled_at' => $booking->scheduled_at->toISOString(),
                'duration_minutes' => $booking->duration_minutes,
                'lesson_type' => $booking->lesson_type,
                'price' => $booking->price,
                'status' => $booking->status,
                'payment_method' => $booking->payment_method,
                'payment_status' => $booking->payment_status,
                'student_notes' => $booking->student_notes,
                'tutor_notes' => $booking->tutor_notes,
                'cancellation_reason' => $booking->cancellation_reason,
                'confirmed_at' => $booking->confirmed_at?->toISOString(),
                'completed_at' => $booking->completed_at?->toISOString(),
                'cancelled_at' => $booking->cancelled_at?->toISOString(),
                'review' => $booking->review ? [
                    'id' => $booking->review->id,
                    'rating' => $booking->review->rating,
                    'comment' => $booking->review->comment,
                    'tutor_reply' => $booking->review->tutor_reply,
                    'created_at' => $booking->review->created_at->toISOString(),
                ] : null,
                'created_at' => $booking->created_at->toISOString(),
            ];
        });

        return response()->json([
            'bookings' => $bookings->items(),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
                'from' => $bookings->firstItem(),
                'to' => $bookings->lastItem(),
            ],
        ]);
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
            'profile' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'created_at' => $user->created_at->toISOString(),
            ]
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
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully',
            'profile' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'updated_at' => $user->updated_at->toISOString(),
            ]
        ]);
    }
}
