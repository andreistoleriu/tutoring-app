<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TutorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user->id,
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'full_name' => $this->user->full_name,
                'email' => $this->when($this->isOwnProfile($request), $this->user->email),
                'phone' => $this->when($this->isOwnProfile($request), $this->user->phone),
            ],
            'bio' => $this->bio,
            'profile_image' => $this->profile_image_url,
            'hourly_rate' => $this->hourly_rate,
            'lesson_types' => $this->lesson_types,
            'offers_online' => $this->offers_online,
            'offers_in_person' => $this->offers_in_person,
            'experience' => $this->experience,
            'education' => $this->education,
            'rating' => round($this->rating, 1),
            'total_reviews' => $this->total_reviews,
            'total_lessons' => $this->total_lessons,
            'is_verified' => $this->is_verified,
            'is_featured' => $this->is_featured,
            'last_active' => $this->last_active?->diffForHumans(),
            'location' => [
                'id' => $this->location->id,
                'city' => $this->location->city,
                'county' => $this->location->county,
                'full_name' => $this->location->full_name,
            ],
            'subjects' => $this->subjects->map(function ($subject) {
                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'slug' => $subject->slug,
                    'experience_description' => $subject->pivot->experience_description,
                ];
            }),
            'reviews' => $this->when($this->relationLoaded('reviews'), function () {
                return $this->reviews->take(5)->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'student_name' => $review->student->first_name,
                        'created_at' => $review->created_at->format('M j, Y'),
                        'tutor_reply' => $review->tutor_reply,
                        'tutor_replied_at' => $review->tutor_replied_at?->format('M j, Y'),
                    ];
                });
            }),
            'availability' => $this->when($this->relationLoaded('availabilities'), function () {
                return $this->availabilities->groupBy('day_of_week')->map(function ($slots) {
                    return $slots->map(function ($slot) {
                        return [
                            'id' => $slot->id,
                            'start_time' => $slot->formatted_start_time,
                            'end_time' => $slot->formatted_end_time,
                            'lesson_type' => $slot->lesson_type,
                        ];
                    });
                });
            }),
            'response_rate' => '95%',
            'average_response_time' => '2 hours',
            'created_at' => $this->created_at->format('M j, Y'),
        ];
    }

    private function isOwnProfile(Request $request): bool
    {
        return $request->user() && $request->user()->id === $this->user_id;
    }
}
