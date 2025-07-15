<?php
// app/Http/Controllers/Api/ReviewController.php - Enhanced Version

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    /**
     * Store a newly created review
     */
    public function store(Request $request): JsonResponse
    {
        Log::info('Review submission attempt', [
            'user_id' => $request->user()->id,
            'user_type' => $request->user()->user_type,
            'request_data' => $request->all()
        ]);

        try {
            // Enhanced validation with better error messages
            $validated = $request->validate([
                'booking_id' => [
                    'required',
                    'integer',
                    'exists:bookings,id'
                ],
                'rating' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:5'
                ],
                'comment' => [
                    'nullable',
                    'string',
                    'max:1000'
                ]
            ], [
                'booking_id.required' => 'ID-ul rezervării este obligatoriu.',
                'booking_id.integer' => 'ID-ul rezervării trebuie să fie un număr.',
                'booking_id.exists' => 'Rezervarea specificată nu există.',
                'rating.required' => 'Evaluarea este obligatorie.',
                'rating.integer' => 'Evaluarea trebuie să fie un număr întreg.',
                'rating.min' => 'Evaluarea trebuie să fie cel puțin 1.',
                'rating.max' => 'Evaluarea nu poate fi mai mare de 5.',
                'comment.string' => 'Comentariul trebuie să fie text.',
                'comment.max' => 'Comentariul nu poate avea mai mult de 1000 de caractere.'
            ]);

            Log::info('Review validation passed', ['validated_data' => $validated]);

            // Verify booking belongs to student and can be reviewed
            $booking = Booking::with(['tutor', 'student', 'subject'])
                ->where('id', $validated['booking_id'])
                ->where('student_id', $request->user()->id)
                ->first();

            if (!$booking) {
                Log::warning('Booking not found or does not belong to user', [
                    'booking_id' => $validated['booking_id'],
                    'user_id' => $request->user()->id
                ]);

                return response()->json([
                    'message' => 'Rezervarea nu a fost găsită sau nu îți aparține.',
                    'errors' => [
                        'booking_id' => ['Rezervarea specificată nu există sau nu îți aparține.']
                    ]
                ], 404);
            }

            // Check if booking is completed
            if ($booking->status !== 'completed') {
                Log::warning('Attempt to review non-completed booking', [
                    'booking_id' => $booking->id,
                    'status' => $booking->status
                ]);

                return response()->json([
                    'message' => 'Doar rezervările finalizate pot fi evaluate.',
                    'errors' => [
                        'booking_id' => ['Această rezervare nu este încă finalizată.']
                    ]
                ], 422);
            }

            // Check if review already exists
            if ($booking->review()->exists()) {
                Log::warning('Attempt to create duplicate review', [
                    'booking_id' => $booking->id,
                    'existing_review_id' => $booking->review->id
                ]);

                return response()->json([
                    'message' => 'Această rezervare a fost deja evaluată.',
                    'errors' => [
                        'booking_id' => ['Ai evaluat deja această rezervare.']
                    ]
                ], 422);
            }

            // Create the review
            $review = Review::create([
                'booking_id' => $validated['booking_id'],
                'student_id' => $request->user()->id,
                'tutor_id' => $booking->tutor_id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
            ]);

            // Load relationships for response
            $review->load(['student', 'tutor', 'booking.subject']);

            Log::info('Review created successfully', [
                'review_id' => $review->id,
                'booking_id' => $booking->id,
                'rating' => $review->rating
            ]);

            return response()->json([
                'message' => 'Review-ul a fost creat cu succes.',
                'review' => $this->formatReviewResponse($review),
            ], 201);

        } catch (ValidationException $e) {
            Log::warning('Review validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'message' => 'Datele introduse nu sunt valide.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Unexpected error creating review', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'message' => 'A apărut o eroare neașteptată. Te rugăm să încerci din nou.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified review
     */
    public function show($id): JsonResponse
    {
        try {
            $review = Review::with(['student', 'tutor', 'booking.subject'])
                ->findOrFail($id);

            return response()->json([
                'review' => $this->formatReviewResponse($review)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching review', [
                'review_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Review-ul nu a fost găsit.'
            ], 404);
        }
    }

    /**
     * Update the specified review
     */
    public function update(Request $request, $id): JsonResponse
    {
        Log::info('Review update attempt', [
            'review_id' => $id,
            'user_id' => $request->user()->id,
            'request_data' => $request->all()
        ]);

        try {
            $validated = $request->validate([
                'rating' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:5'
                ],
                'comment' => [
                    'nullable',
                    'string',
                    'max:1000'
                ]
            ], [
                'rating.required' => 'Evaluarea este obligatorie.',
                'rating.integer' => 'Evaluarea trebuie să fie un număr întreg.',
                'rating.min' => 'Evaluarea trebuie să fie cel puțin 1.',
                'rating.max' => 'Evaluarea nu poate fi mai mare de 5.',
                'comment.string' => 'Comentariul trebuie să fie text.',
                'comment.max' => 'Comentariul nu poate avea mai mult de 1000 de caractere.'
            ]);

            $review = Review::with(['student', 'tutor', 'booking.subject'])
                ->where('student_id', $request->user()->id)
                ->findOrFail($id);

            $review->update($validated);

            Log::info('Review updated successfully', [
                'review_id' => $review->id,
                'rating' => $review->rating
            ]);

            return response()->json([
                'message' => 'Review-ul a fost actualizat cu succes.',
                'review' => $this->formatReviewResponse($review),
            ]);

        } catch (ValidationException $e) {
            Log::warning('Review update validation failed', [
                'review_id' => $id,
                'errors' => $e->errors()
            ]);

            return response()->json([
                'message' => 'Datele introduse nu sunt valide.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error updating review', [
                'review_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Review-ul nu a fost găsit sau nu ai permisiunea să îl modifici.'
            ], 404);
        }
    }

    /**
     * Remove the specified review
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        Log::info('Review deletion attempt', [
            'review_id' => $id,
            'user_id' => $request->user()->id
        ]);

        try {
            $review = Review::where('student_id', $request->user()->id)
                ->findOrFail($id);

            $review->delete();

            Log::info('Review deleted successfully', [
                'review_id' => $id
            ]);

            return response()->json([
                'message' => 'Review-ul a fost șters cu succes.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting review', [
                'review_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Review-ul nu a fost găsit sau nu ai permisiunea să îl ștergi.'
            ], 404);
        }
    }

    /**
     * Reply to a review (tutor only)
     */
    public function reply(Request $request, Review $review): JsonResponse
    {
        Log::info('Review reply attempt', [
            'review_id' => $review->id,
            'user_id' => $request->user()->id,
            'user_type' => $request->user()->user_type
        ]);

        try {
            $user = $request->user();

            // Make sure the user is a tutor
            if (!$user->isTutor()) {
                return response()->json([
                    'message' => 'Doar tutorii pot răspunde la review-uri.'
                ], 403);
            }

            // Make sure the review belongs to this tutor's booking
            if ($review->booking->tutor_id !== $user->id) {
                return response()->json([
                    'message' => 'Nu ai permisiunea să răspunzi la acest review.'
                ], 403);
            }

            $validated = $request->validate([
                'reply' => [
                    'required',
                    'string',
                    'max:1000'
                ]
            ], [
                'reply.required' => 'Răspunsul este obligatoriu.',
                'reply.string' => 'Răspunsul trebuie să fie text.',
                'reply.max' => 'Răspunsul nu poate avea mai mult de 1000 de caractere.'
            ]);

            $review->update([
                'tutor_reply' => $validated['reply'],
                'tutor_replied_at' => now(),
            ]);

            Log::info('Review reply created successfully', [
                'review_id' => $review->id,
                'tutor_id' => $user->id
            ]);

            return response()->json([
                'message' => 'Răspunsul a fost trimis cu succes.',
                'review' => $this->formatReviewResponse($review->fresh(['student', 'tutor', 'booking.subject'])),
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Datele introduse nu sunt valide.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error creating review reply', [
                'review_id' => $review->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'A apărut o eroare. Te rugăm să încerci din nou.'
            ], 500);
        }
    }

    /**
     * Format review response data
     */
    private function formatReviewResponse(Review $review): array
    {
        return [
            'id' => $review->id,
            'rating' => $review->rating,
            'comment' => $review->comment,
            'tutor_reply' => $review->tutor_reply,
            'tutor_replied_at' => $review->tutor_replied_at?->toISOString(),
            'created_at' => $review->created_at->toISOString(),
            'updated_at' => $review->updated_at->toISOString(),
            'student' => [
                'id' => $review->student->id,
                'first_name' => $review->student->first_name,
                'last_name' => $review->student->last_name,
                'full_name' => $review->student->full_name,
            ],
            'tutor' => [
                'id' => $review->tutor->id,
                'first_name' => $review->tutor->first_name,
                'last_name' => $review->tutor->last_name,
                'full_name' => $review->tutor->full_name,
            ],
            'booking' => [
                'id' => $review->booking->id,
                'scheduled_at' => $review->booking->scheduled_at,
                'duration_minutes' => $review->booking->duration_minutes,
                'lesson_type' => $review->booking->lesson_type,
                'price' => $review->booking->price,
                'subject' => [
                    'id' => $review->booking->subject->id,
                    'name' => $review->booking->subject->name,
                ]
            ]
        ];
    }
}
