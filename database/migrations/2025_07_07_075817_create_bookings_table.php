<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('tutor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->datetime('scheduled_at');
            $table->integer('duration_minutes')->default(60);
            $table->enum('lesson_type', ['online', 'in_person']);
            $table->decimal('price', 8, 2);
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])
                ->default('pending');
            $table->enum('payment_method', ['card', 'cash']);
            $table->enum('payment_status', ['pending', 'paid', 'failed'])
                ->default('pending');
            $table->text('student_notes')->nullable();
            $table->text('tutor_notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
