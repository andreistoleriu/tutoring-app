<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'student_id',
        'tutor_id',
        'rating',
        'comment',
        'tutor_reply',
        'tutor_replied_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'tutor_replied_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function hasReply(): bool
    {
        return !is_null($this->tutor_reply);
    }

    public function addTutorReply(string $reply): bool
    {
        return $this->update([
            'tutor_reply' => $reply,
            'tutor_replied_at' => now(),
        ]);
    }

    public function getStarsAttribute(): array
    {
        $stars = [];
        for ($i = 1; $i <= 5; $i++) {
            $stars[] = $i <= $this->rating;
        }
        return $stars;
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($review) {
            $review->tutor->load('tutor')->tutor->updateRating();
        });

        static::updated(function ($review) {
            $review->tutor->load('tutor')->tutor->updateRating();
        });

        static::deleted(function ($review) {
            $review->tutor->load('tutor')->tutor->updateRating();
        });
    }
}
