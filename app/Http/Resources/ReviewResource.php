<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'tutor_reply' => $this->tutor_reply,
            'tutor_replied_at' => $this->tutor_replied_at?->format('M j, Y'),
            'has_reply' => $this->hasReply(),
            'stars' => $this->stars,
            'student' => [
                'id' => $this->student->id,
                'first_name' => $this->student->first_name,
                'last_name' => $this->student->last_name,
                'full_name' => $this->student->full_name,
            ],
            'tutor' => [
                'id' => $this->tutor->id,
                'first_name' => $this->tutor->first_name,
                'last_name' => $this->tutor->last_name,
                'full_name' => $this->tutor->full_name,
            ],
            'booking' => [
                'id' => $this->booking->id,
                'subject' => $this->booking->subject->name,
                'lesson_type' => $this->booking->lesson_type,
                'scheduled_at' => $this->booking->scheduled_at->format('M j, Y'),
            ],
            'created_at' => $this->created_at->format('M j, Y'),
            'updated_at' => $this->updated_at->format('M j, Y'),
        ];
    }
}
