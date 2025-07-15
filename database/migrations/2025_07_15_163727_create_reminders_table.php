<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('type'); // lesson_reminder, review_reminder, payment_reminder, etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data for the reminder
            $table->timestamp('scheduled_at');
            $table->timestamp('sent_at')->nullable();
            $table->boolean('is_sent')->default(false);
            $table->boolean('is_read')->default(false);
            $table->string('channel')->default('email'); // email, sms, push, in_app
            $table->timestamps();

            $table->index(['user_id', 'is_sent', 'scheduled_at']);
            $table->index(['booking_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
