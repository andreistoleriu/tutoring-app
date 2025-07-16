<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Services\ReminderService;

class BookingController extends Controller
{
        public function __construct(ReminderService $reminderService)
    {
        $this->reminderService = $reminderService;
    }

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

    // Verify user is a student
    if (!$request->user()->isStudent()) {
        return response()->json([
            'message' => 'Only students can create bookings.',
        ], 403);
    }

    // Verify tutor exists and is active
    $tutor = User::where('id', $validated['tutor_id'])
        ->where('user_type', 'tutor')
        ->where('is_active', true)
        ->firstOrFail();

    if (!$tutor->tutor || !$tutor->tutor->is_active) {
        return response()->json([
            'message' => 'This tutor is not available for bookings.',
        ], 422);
    }

    // Check if tutor teaches the subject
    if (!$tutor->tutor->subjects()->where('subject_id', $validated['subject_id'])->exists()) {
        return response()->json([
            'message' => 'This tutor does not teach the selected subject.',
        ], 422);
    }

    // Validate lesson type availability
    if ($validated['lesson_type'] === 'online' && !$tutor->tutor->offers_online) {
        return response()->json([
            'message' => 'This tutor does not offer online lessons.',
        ], 422);
    }

    if ($validated['lesson_type'] === 'in_person' && !$tutor->tutor->offers_in_person) {
        return response()->json([
            'message' => 'This tutor does not offer in-person lessons.',
        ], 422);
    }

    // Validate payment method for lesson type
    if ($validated['lesson_type'] === 'online' && $validated['payment_method'] === 'cash') {
        return response()->json([
            'message' => 'Cash payment is only available for in-person lessons.',
        ], 422);
    }

    // Check for time slot availability
    $scheduledAt = Carbon::parse($validated['scheduled_at']);
    $endTime = $scheduledAt->copy()->addMinutes($validated['duration_minutes']);

    // Strict availability validation - only allow booking during tutor's set hours
    $dayOfWeek = strtolower($scheduledAt->format('l')); // monday, tuesday, etc.
    $timeSlot = $scheduledAt->format('H:i');

    // Check if tutor has availability for this specific time and lesson type
    $hasAvailability = $tutor->tutor->availabilities()
        ->where('day_of_week', $dayOfWeek)
        ->where('is_active', true)
        ->where('start_time', '<=', $timeSlot)
        ->where('end_time', '>=', $endTime->format('H:i'))
        ->where(function ($query) use ($validated) {
            $query->where('lesson_type', $validated['lesson_type'])
                  ->orWhere('lesson_type', 'both');
        })
        ->exists();

    if (!$hasAvailability) {
        return response()->json([
            'message' => 'This time slot is not available for the selected lesson type. Please check the tutor\'s availability.',
        ], 422);
    }

    // Check for conflicting bookings
    $conflictingBooking = $tutor->tutorBookings()
        ->where('status', '!=', 'cancelled')
        ->where(function ($query) use ($scheduledAt, $endTime) {
            $query->whereBetween('scheduled_at', [$scheduledAt, $endTime])
                  ->orWhere(function ($q) use ($scheduledAt, $endTime) {
                      $q->where('scheduled_at', '<', $scheduledAt)
                        ->whereRaw('DATE_ADD(scheduled_at, INTERVAL duration_minutes MINUTE) > ?', [$scheduledAt]);
                  });
        })
        ->exists();

    if ($conflictingBooking) {
        return response()->json([
            'message' => 'This time slot is already booked. Please select another time.',
        ], 422);
    }

    // Check for conflicting bookings
    $conflictingBooking = $tutor->tutorBookings()
        ->where('status', '!=', 'cancelled')
        ->where(function ($query) use ($scheduledAt, $endTime) {
            $query->whereBetween('scheduled_at', [$scheduledAt, $endTime])
                  ->orWhere(function ($q) use ($scheduledAt, $endTime) {
                      $q->where('scheduled_at', '<', $scheduledAt)
                        ->whereRaw('DATE_ADD(scheduled_at, INTERVAL duration_minutes MINUTE) > ?', [$scheduledAt]);
                  });
        })
        ->exists();

    if ($conflictingBooking) {
        return response()->json([
            'message' => 'This time slot is already booked.',
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
        'status' => 'pending',
        'payment_status' => 'pending',
    ]);

    $booking->load(['student', 'tutor', 'subject']);

    // TODO: Send notification to tutor (implement later)
    // event(new BookingCreated($booking));

     try {
        $this->reminderService->createLessonReminders($booking);
        \Log::info('Reminders created for new booking', [
            'booking_id' => $booking->id,
            'student_id' => $booking->student_id,
            'tutor_id' => $booking->tutor_id,
            'scheduled_at' => $booking->scheduled_at
        ]);
    } catch (\Exception $e) {
        \Log::error('Failed to create reminders for booking', [
            'booking_id' => $booking->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        // Don't fail the booking creation if reminder creation fails
    }

    return response()->json([
        'message' => 'Booking created successfully',
        'booking' => [
            'id' => $booking->id,
            'student' => [
                'id' => $booking->student->id,
                'first_name' => $booking->student->first_name,
                'last_name' => $booking->student->last_name,
                'email' => $booking->student->email,
            ],
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
            'price' => $booking->price,
            'status' => $booking->status,
            'payment_method' => $booking->payment_method,
            'payment_status' => $booking->payment_status,
            'student_notes' => $booking->student_notes,
            'created_at' => $booking->created_at->toISOString(),
        ],
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

        $this->reminderService->createLessonReminders($booking);

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

        app(ReminderService::class)->createReviewReminder($booking);

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

public function update(Request $request, $id): JsonResponse
{
    $user = $request->user();

    // Find booking that belongs to the student
    $booking = Booking::where('student_id', $user->id)
        ->findOrFail($id);

    if (!$booking->canBeEdited()) {
        return response()->json([
            'message' => 'Booking cannot be edited. Editing is only allowed for pending bookings until 24 hours before the lesson.',
        ], 422);
    }

    $validated = $request->validate([
        'subject_id' => 'sometimes|exists:subjects,id',        // ADD THIS LINE
        'scheduled_at' => 'required|date|after:now',
        'duration_minutes' => 'sometimes|integer|in:30,60,90,120',
        'lesson_type' => 'sometimes|in:online,in_person',
        'payment_method' => 'sometimes|in:card,cash',
        'student_notes' => 'nullable|string|max:1000',
    ]);

    // Check if tutor teaches the requested subject (if subject is being changed)
    if (isset($validated['subject_id'])) {
        $tutor = $booking->tutor->tutor;
        $tutorTeachesSubject = $tutor->subjects()
            ->where('subject_id', $validated['subject_id'])
            ->exists();

        if (!$tutorTeachesSubject) {
            return response()->json([
                'message' => 'Tutor does not teach this subject.',
            ], 422);
        }
    }

    // Check if tutor still offers the requested lesson type
    $tutor = $booking->tutor->tutor;
    if (isset($validated['lesson_type'])) {
        if ($validated['lesson_type'] === 'online' && !$tutor->offers_online) {
            return response()->json([
                'message' => 'Tutor no longer offers online lessons.',
            ], 422);
        }
        if ($validated['lesson_type'] === 'in_person' && !$tutor->offers_in_person) {
            return response()->json([
                'message' => 'Tutor no longer offers in-person lessons.',
            ], 422);
        }
    }

    // Check for time conflicts if schedule is changed
    if (isset($validated['scheduled_at'])) {
        $scheduledAt = Carbon::parse($validated['scheduled_at']);
        $duration = $validated['duration_minutes'] ?? $booking->duration_minutes;
        $endTime = $scheduledAt->copy()->addMinutes($duration);

        // Check for conflicting bookings (excluding current booking)
        $conflictingBooking = $booking->tutor->tutorBookings()
            ->where('id', '!=', $booking->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($scheduledAt, $endTime) {
                $query->whereBetween('scheduled_at', [$scheduledAt, $endTime])
                      ->orWhere(function ($q) use ($scheduledAt, $endTime) {
                          $q->where('scheduled_at', '<', $scheduledAt)
                            ->whereRaw('DATE_ADD(scheduled_at, INTERVAL duration_minutes MINUTE) > ?', [$scheduledAt]);
                      });
            })
            ->exists();

        if ($conflictingBooking) {
            return response()->json([
                'message' => 'This time slot is already booked. Please select another time.',
            ], 422);
        }
    }

    // Calculate new price if duration or lesson type changed
    $price = $booking->price;
    if (isset($validated['duration_minutes']) || isset($validated['lesson_type'])) {
        $duration = $validated['duration_minutes'] ?? $booking->duration_minutes;
        $price = ($duration / 60) * $tutor->hourly_rate;
    }

    // Update booking - THIS WILL NOW INCLUDE subject_id
    $booking->update([
        ...$validated,
        'price' => $price,
    ]);

    $booking->load(['student', 'tutor', 'subject']);

    return response()->json([
        'message' => 'Booking updated successfully',
        'booking' => $booking,
    ]);
}
}
