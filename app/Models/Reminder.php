<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'booking_id',
        'type',
        'title',
        'message',
        'data',
        'scheduled_at',
        'sent_at',
        'is_sent',
        'is_read',
        'channel',
    ];

    protected $casts = [
        'data' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'is_sent' => 'boolean',
        'is_read' => 'boolean',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('is_sent', false)
                    ->where('scheduled_at', '<=', now());
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    // Methods
    public function markAsSent(): bool
    {
        return $this->update([
            'is_sent' => true,
            'sent_at' => now(),
        ]);
    }

    public function markAsRead(): bool
    {
        return $this->update(['is_read' => true]);
    }

    public function isDue(): bool
    {
        return !$this->is_sent && $this->scheduled_at <= now();
    }

    public function getTimeUntil(): string
    {
        if ($this->scheduled_at < now()) {
            return 'Overdue';
        }

        return $this->scheduled_at->diffForHumans();
    }
}
