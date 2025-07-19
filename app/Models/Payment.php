<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'subscription_id',
        'user_id',
        'amount',
        'currency',
        'payment_type',
        'status',
        'netopia_transaction_id',
        'netopia_order_id',
        'netopia_response',
        'payment_data',
        'processed_at',
        'failed_at',
        'failure_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'netopia_response' => 'array',
        'payment_data' => 'array',
        'processed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    // Relationships
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Status methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'processed_at' => now(),
        ]);
    }

    public function markAsFailed(string $reason): void
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'failure_reason' => $reason,
        ]);
    }

    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }
}
