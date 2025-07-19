<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Services\NetopiaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    private $netopiaService;

    public function __construct(NetopiaService $netopiaService)
    {
        $this->netopiaService = $netopiaService;
    }

    /**
     * Get current user's subscription
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->activeSubscription;

        if (!$subscription) {
            // Create free trial subscription if none exists
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_type' => 'free_trial',
                'price' => 0,
                'status' => 'active',
                'started_at' => now(),
                'trial_ends_at' => now()->addDays(14),
                'expires_at' => now()->addDays(14),
                'shows_ads' => true,
            ]);
        }

        return response()->json([
            'subscription' => [
                'id' => $subscription->id,
                'plan_type' => $subscription->plan_type,
                'plan_name' => $subscription->plan_name,
                'price' => $subscription->price,
                'currency' => $subscription->currency,
                'status' => $subscription->status,
                'shows_ads' => $subscription->shows_ads,
                'days_remaining' => $subscription->days_remaining,
                'trial_days_remaining' => $subscription->trial_days_remaining,
                'is_in_trial' => $subscription->isInTrial(),
                'features' => $subscription->plan_features,
                'expires_at' => $subscription->expires_at,
                'started_at' => $subscription->started_at,
            ],
            'available_plans' => $this->getAvailablePlans(),
        ]);
    }

    /**
     * Upgrade to premium
     */
    public function upgrade(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->activeSubscription;

        if (!$subscription) {
            return response()->json(['message' => 'No active subscription found'], 404);
        }

        if ($subscription->plan_type === 'premium' && $subscription->isActive()) {
            return response()->json([
                'message' => 'Already have premium subscription',
                'subscription' => $subscription,
            ], 400);
        }

        try {
            // Create payment with Netopia
            $result = $this->netopiaService->createSubscriptionPayment($user, 'premium');

            if (!$result['success']) {
                return response()->json([
                    'message' => 'Failed to create payment',
                    'error' => $result['error'],
                ], 500);
            }

            return response()->json([
                'message' => 'Payment created successfully',
                'payment_url' => $result['payment_url'],
                'payment_id' => $result['payment_id'],
                'amount' => Subscription::PLANS['premium']['price'],
                'currency' => 'EUR',
            ]);

        } catch (\Exception $e) {
            Log::error('Subscription upgrade failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Upgrade failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request): JsonResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $subscription = $user->activeSubscription;

        if (!$subscription) {
            return response()->json(['message' => 'No active subscription found'], 404);
        }

        if ($subscription->plan_type === 'free_trial') {
            return response()->json(['message' => 'Cannot cancel free trial'], 400);
        }

        $reason = $request->input('reason');
        $subscription->cancel($reason);

        return response()->json([
            'message' => 'Subscription cancelled successfully',
            'subscription' => [
                'id' => $subscription->id,
                'status' => $subscription->status,
                'cancelled_at' => $subscription->cancelled_at,
                'cancellation_reason' => $subscription->cancellation_reason,
            ],
        ]);
    }

    /**
     * Get subscription plans
     */
    public function plans(): JsonResponse
    {
        return response()->json([
            'plans' => $this->getAvailablePlans(),
        ]);
    }

    /**
     * Get available plans
     */
    private function getAvailablePlans(): array
    {
        return [
            'free_trial' => [
                'name' => 'Free Trial with Ads',
                'price' => 0,
                'currency' => 'EUR',
                'duration' => '14 days',
                'features' => Subscription::PLANS['free_trial']['features'],
                'shows_ads' => true,
            ],
            'premium' => [
                'name' => 'Premium - Ad Free',
                'price' => 3.49,
                'currency' => 'EUR',
                'duration' => '30 days',
                'features' => Subscription::PLANS['premium']['features'],
                'shows_ads' => false,
            ],
        ];
    }
}
