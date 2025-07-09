<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Verify booking belongs to student and can be reviewed
        $booking = Booking::where('id', $validated['booking_id'])
            ->where('student_id', $request->user()->id)
            ->where('status', 'completed')
            ->firstOrFail();

        // Check if review already exists
        if ($booking->review()->exists()) {
            return response()->json([
                'message' => 'This booking has already been reviewed.',
            ], 422);
        }

        $review = Review::create([
            'booking_id' => $validated['booking_id'],
            'student_id' => $request->user()->id,
            'tutor_id' => $booking->tutor_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        $review->load(['student', 'tutor', 'booking']);

        return response()->json([
            'message' => 'Review created successfully',
            'review' => $review,
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $review = Review::with(['student', 'tutor', 'booking'])
            ->findOrFail($id);

        return response()->json(['review' => $review]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review = Review::where('student_id', $request->user()->id)
            ->findOrFail($id);

        $review->update($validated);
        $review->load(['student', 'tutor', 'booking']);

        return response()->json([
            'message' => 'Review updated successfully',
            'review' => $review,
        ]);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $review = Review::where('student_id', $request->user()->id)
            ->findOrFail($id);

        $review->delete();

        return response()->json([
            'message' => 'Review deleted successfully',
        ]);
    }

   public function reply(Request $request, Review $review): JsonResponse
    {
        $user = $request->user();

        // Make sure the review belongs to this tutor's booking
        if ($review->booking->tutor_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'reply' => 'required|string|max:1000',
        ]);

        $review->update([
            'tutor_reply' => $validated['reply'],
            'tutor_reply_at' => now(),
        ]);

        return response()->json([
            'message' => 'Reply submitted successfully',
            'review' => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'student' => [
                    'id' => $review->student->id,
                    'first_name' => $review->student->first_name,
                    'last_name' => $review->student->last_name,
                    'full_name' => $review->student->first_name . ' ' . $review->student->last_name,
                ],
                'subject' => [
                    'id' => $review->booking->subject->id,
                    'name' => $review->booking->subject->name,
                ],
                'created_at' => $review->created_at->toISOString(),
                'reply' => $review->tutor_reply,
                'reply_date' => $review->tutor_reply_at->toISOString(),
            ]
        ]);
    }
}
