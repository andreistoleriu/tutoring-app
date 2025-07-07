<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = $user->isTutor()
            ? $user->tutorBookings()
            : $user->studentBookings();

        $query->with(['student', 'tutor', 'subject']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type (upcoming, past, etc.)
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

        return response()->json([
            'bookings' => $bookings->items(),
            'pagination' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tutor_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:30|max:180',
            'lesson_type' => ['required', Rule::in(['online', 'in_person'])],
            'payment_method' => ['required', Rule::in(['card', 'cash'])],
            'student_notes' => 'nullable|string|max:500',
        ]);

        // Verify tutor exists and is active
        $tutor = User::where('id', $validated['tutor_id'])
            ->where('user_type', 'tutor')
            ->where('is_active', true)
            ->firstOrFail();

        // Check if tutor teaches the subject
        if (!$tutor->tutor->subjects()->where('subject_id', $validated['subject_id'])->exists()) {
            return response()->json([
                'message' => 'This tutor does not teach the selected subject.',
            ], 422);
        }

        // Calculate price
        $price = $tutor->tutor->hourly_rate * ($validated['duration_minutes'] / 60);

        // Create booking
        $booking = Booking::create([
            'student_id' => $request->user()->id,
            'tutor_id' => $validated['tutor_id'],
            'subject_id' => $validated['subject_id'],
            'scheduled_at' => $validated['scheduled_at'],
            'duration_minutes' => $validated['duration_minutes'],
            'lesson_type' => $validated['lesson_type'],
            'price' => $price,
            'payment_method' => $validated['payment_method'],
            'student_notes' => $validated['student_notes'],
        ]);

        $booking->load(['student', 'tutor', 'subject']);

        return response()->json([
            'message' => 'Booking created successfully',
            'booking' => $booking,
        ], 201);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        $booking = Booking::with(['student', 'tutor', 'subject', 'review'])
            ->where(function ($query) use ($user) {
                $query->where('student_id', $user->id)
                      ->orWhere('tutor_id', $user->id);
            })
            ->findOrFail($id);

        return response()->json(['booking' => $booking]);
    }

    public function confirm(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        $booking = Booking::where('tutor_id', $user->id)
            ->findOrFail($id);

        if (!$booking->canBeConfirmed()) {
            return response()->json([
                'message' => 'Booking cannot be confirmed.',
            ], 422);
        }

        $booking->confirm();
        $booking->load(['student', 'tutor', 'subject']);

        return response()->json([
            'message' => 'Booking confirmed successfully',
            'booking' => $booking,
        ]);
    }

    public function complete(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        $booking = Booking::where('tutor_id', $user->id)
            ->findOrFail($id);

        if (!$booking->canBeCompleted()) {
            return response()->json([
                'message' => 'Booking cannot be completed.',
            ], 422);
        }

        $booking->complete();

        // Update tutor stats
        $tutor = $user->tutor;
        $tutor->increment('total_lessons');

        if ($booking->payment_status === 'paid') {
            $tutor->increment('total_earnings', $booking->price);
        }

        $booking->load(['student', 'tutor', 'subject']);

        return response()->json([
            'message' => 'Booking completed successfully',
            'booking' => $booking,
        ]);
    }

    public function cancel(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        $booking = Booking::where(function ($query) use ($user) {
                $query->where('student_id', $user->id)
                      ->orWhere('tutor_id', $user->id);
            })
            ->findOrFail($id);

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        if (!$booking->canBeCancelled()) {
            return response()->json([
                'message' => 'Booking cannot be cancelled. Cancellation is only allowed 24 hours before the lesson.',
            ], 422);
        }

        $booking->cancel($validated['cancellation_reason']);
        $booking->load(['student', 'tutor', 'subject']);

        return response()->json([
            'message' => 'Booking cancelled successfully',
            'booking' => $booking,
        ]);
    }

    public function confirmPayment(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        $booking = Booking::where('tutor_id', $user->id)
            ->where('payment_method', 'cash')
            ->where('payment_status', 'pending')
            ->findOrFail($id);

        $booking->markAsPaid();

        // Update tutor earnings
        $user->tutor->increment('total_earnings', $booking->price);

        $booking->load(['student', 'tutor', 'subject']);

        return response()->json([
            'message' => 'Payment confirmed successfully',
            'booking' => $booking,
        ]);
    }
}
