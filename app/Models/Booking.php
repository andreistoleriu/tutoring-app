<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'tutor_id',
        'subject_id',
        'scheduled_at',
        'duration_minutes',
        'lesson_type',
        'price',
        'status',
        'payment_method',
        'payment_status',
        'student_notes',
        'tutor_notes',
        'cancellation_reason',
        'confirmed_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'price' => 'decimal:2',
        'duration_minutes' => 'integer',
        'confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    // Accessors
    public function getEndTimeAttribute(): Carbon
    {
        return $this->scheduled_at->addMinutes($this->duration_minutes);
    }

    // Helper methods
    public function isUpcoming(): bool
    {
        return $this->scheduled_at->isFuture() &&
               in_array($this->status, ['pending', 'confirmed']);
    }

    public function isPast(): bool
    {
        return $this->scheduled_at->isPast();
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']) &&
               $this->scheduled_at->diffInHours(now()) > 24;
    }

    public function canBeConfirmed(): bool
    {
        return $this->status === 'pending';
    }

    public function canBeCompleted(): bool
    {
        return $this->status === 'confirmed' &&
               $this->scheduled_at->isPast();
    }

    public function canBeReviewed(): bool
    {
        return $this->status === 'completed' &&
               !$this->review()->exists();
    }

    // Actions
    public function confirm(): bool
    {
        if ($this->canBeConfirmed()) {
            return $this->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
            ]);
        }

        return false;
    }

    public function complete(): bool
    {
        if ($this->canBeCompleted()) {
            return $this->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        return false;
    }

    public function cancel(string $reason = null): bool
    {
        if ($this->canBeCancelled()) {
            return $this->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);
        }

        return false;
    }

    public function markAsPaid(): bool
    {
        return $this->update([
            'payment_status' => 'paid',
        ]);
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>', now())
                    ->whereIn('status', ['pending', 'confirmed']);
    }

    public function scopePast($query)
    {
        return $query->where('scheduled_at', '<', now());
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
