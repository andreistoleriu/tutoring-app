<?php

// app/Http/Middleware/CheckSubscription.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $subscription = $user->activeSubscription;

        // If no subscription exists, create free trial
        if (!$subscription) {
            $subscription = $user->subscription()->create([
                'plan_type' => 'free_trial',
                'price' => 0,
                'status' => 'active',
                'started_at' => now(),
                'trial_ends_at' => now()->addDays(14),
                'expires_at' => now()->addDays(14),
                'shows_ads' => true,
            ]);
        }

        // Check if subscription has expired
        if ($subscription->hasExpired()) {
            // If trial expired, downgrade to show ads
            if ($subscription->plan_type === 'free_trial') {
                $subscription->update([
                    'status' => 'expired',
                    'shows_ads' => true,
                ]);

                return response()->json([
                    'message' => 'Your free trial has expired. Please upgrade to continue using premium features.',
                    'subscription_expired' => true,
                    'subscription' => $subscription,
                ], 402); // Payment Required
            }

            // If premium expired, downgrade to trial with ads
            $subscription->update([
                'plan_type' => 'free_trial',
                'status' => 'expired',
                'shows_ads' => true,
            ]);
        }

        // Add subscription data to request
        $request->merge(['user_subscription' => $subscription]);

        return $next($request);
    }
}
