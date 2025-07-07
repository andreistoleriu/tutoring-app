<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Availability extends Model
{
    use HasFactory;

    protected $fillable = [
        'tutor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'lesson_type',
        'is_active',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_active' => 'boolean',
    ];

    const DAYS_OF_WEEK = [
        'monday' => 1,
        'tuesday' => 2,
        'wednesday' => 3,
        'thursday' => 4,
        'friday' => 5,
        'saturday' => 6,
        'sunday' => 0,
    ];

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function getDayNumberAttribute(): int
    {
        return self::DAYS_OF_WEEK[$this->day_of_week] ?? 0;
    }

    public function getFormattedStartTimeAttribute(): string
    {
        return Carbon::parse($this->start_time)->format('H:i');
    }

    public function getFormattedEndTimeAttribute(): string
    {
        return Carbon::parse($this->end_time)->format('H:i');
    }

    public function getDurationMinutesAttribute(): int
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        return $end->diffInMinutes($start);
    }

    public function supportsLessonType(string $lessonType): bool
    {
        return $this->lesson_type === 'both' || $this->lesson_type === $lessonType;
    }

    public function generateTimeSlots(int $slotDuration = 60): array
    {
        $slots = [];
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        while ($start->addMinutes($slotDuration)->lte($end)) {
            $slotStart = $start->copy()->subMinutes($slotDuration);
            $slotEnd = $start->copy();

            $slots[] = [
                'start' => $slotStart->format('H:i'),
                'end' => $slotEnd->format('H:i'),
                'start_time' => $slotStart,
                'end_time' => $slotEnd,
            ];
        }

        return $slots;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDay($query, string $day)
    {
        return $query->where('day_of_week', $day);
    }

    public function scopeForLessonType($query, string $lessonType)
    {
        return $query->where(function ($q) use ($lessonType) {
            $q->where('lesson_type', $lessonType)
              ->orWhere('lesson_type', 'both');
        });
    }
}
