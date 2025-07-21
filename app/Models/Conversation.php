<?php
// app/Models/Conversation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'tutor_id',
        'student_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function unreadMessagesFor(User $user): HasMany
    {
        return $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at');
    }

    public function getUnreadCountFor(User $user): int
    {
        return $this->unreadMessagesFor($user)->count();
    }

    public function getOtherParticipant(User $user): User
    {
        return $user->id === $this->tutor_id ? $this->student : $this->tutor;
    }

    public function markAsReadFor(User $user): void
    {
        $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public static function findOrCreateBetween(User $tutor, User $student): self
    {
        return static::firstOrCreate([
            'tutor_id' => $tutor->id,
            'student_id' => $student->id,
        ]);
    }
}
