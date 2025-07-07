<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'scheduled_at' => $this->scheduled_at->format('Y-m-d H:i:s'),
            'scheduled_date' => $this->scheduled_at->format('Y-m-d'),
            'scheduled_time' => $this->scheduled_at->format('H:i'),
            'end_time' => $this->end_time->format('H:i'),
            'formatted_date' => $this->scheduled_at->format('M j, Y'),
            'formatted_time' => $this->scheduled_at->format('g:i A'),
            'formatted_datetime' => $this->scheduled_at->format('M j, Y \a\t g:i A'),
            'duration_minutes' => $this->duration_minutes,
            'duration_hours' => $this->duration_minutes / 60,
            'lesson_type' => $this->lesson_type,
            'lesson_type_label' => ucfirst(str_replace('_', ' ', $this->lesson_type)),
            'price' => $this->price,
            'formatted_price' => $this->price . ' RON',
            'status' => $this->status,
            'status_label' => ucfirst($this->status),
            'payment_method' => $this->payment_method,
            'payment_method_label' => ucfirst($this->payment_method),
            'payment_status' => $this->payment_status,
            'payment_status_label' => ucfirst($this->payment_status),
            'student_notes' => $this->student_notes,
            'tutor_notes' => $this->tutor_notes,
            'cancellation_reason' => $this->cancellation_reason,
            'student' => [
                'id' => $this->student->id,
                'first_name' => $this->student->first_name,
                'last_name' => $this->student->last_name,
                'full_name' => $this->student->full_name,
                'email' => $this->when($this->canViewContactInfo($request), $this->student->email),
                'phone' => $this->when($this->canViewContactInfo($request), $this->student->phone),
            ],
            'tutor' => [
                'id' => $this->tutor->id,
                'first_name' => $this->tutor->first_name,
                'last_name' => $this->tutor->last_name,
                'full_name' => $this->tutor->full_name,
                'email' => $this->when($this->canViewContactInfo($request), $this->tutor->email),
                'phone' => $this->when($this->canViewContactInfo($request), $this->tutor->phone),
                'profile_image' => $this->tutor->tutor->profile_image_url ?? null,
            ],
            'subject' => [
                'id' => $this->subject->id,
                'name' => $this->subject->name,
                'slug' => $this->subject->slug,
            ],
            'review' => $this->when($this->relationLoaded('review') && $this->review, function () {
                return [
                    'id' => $this->review->id,
                    'rating' => $this->review->rating,
                    'comment' => $this->review->comment,
                    'tutor_reply' => $this->review->tutor_reply,
                    'created_at' => $this->review->created_at->format('M j, Y'),
                ];
            }),
            'can_be_cancelled' => $this->canBeCancelled(),
            'can_be_confirmed' => $this->canBeConfirmed(),
            'can_be_completed' => $this->canBeCompleted(),
            'can_be_reviewed' => $this->canBeReviewed(),
            'is_upcoming' => $this->isUpcoming(),
            'is_past' => $this->isPast(),
            'time_until_lesson' => $this->getTimeUntilLesson(),
            'confirmed_at' => $this->confirmed_at?->format('M j, Y \a\t g:i A'),
            'completed_at' => $this->completed_at?->format('M j, Y \a\t g:i A'),
            'cancelled_at' => $this->cancelled_at?->format('M j, Y \a\t g:i A'),
            'created_at' => $this->created_at->format('M j, Y \a\t g:i A'),
        ];
    }

    private function canViewContactInfo(Request $request): bool
    {
        $user = $request->user();

        if (!$user) {
            return false;
        }

        return ($user->id === $this->student_id || $user->id === $this->tutor_id) &&
               in_array($this->status, ['confirmed', 'completed']);
    }

    private function getTimeUntilLesson(): ?string
    {
        if ($this->isPast()) {
            return null;
        }

        return $this->scheduled_at->diffForHumans();
    }
}
