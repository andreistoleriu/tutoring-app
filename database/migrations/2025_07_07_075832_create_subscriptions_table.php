<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Changed from tutor_id to user_id
            $table->enum('plan_type', ['free_trial', 'premium']);
            $table->decimal('price', 8, 2)->default(0);
            $table->string('currency', 3)->default('EUR');
            $table->enum('status', ['active', 'expired', 'cancelled', 'suspended'])
                ->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->boolean('shows_ads')->default(true); // New field for ad control
            $table->string('netopia_subscription_id')->nullable(); // Changed from stripe
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
