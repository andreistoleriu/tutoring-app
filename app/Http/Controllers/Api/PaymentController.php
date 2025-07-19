<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\NetopiaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    private $netopiaService;

    public function __construct(NetopiaService $netopiaService)
    {
        $this->netopiaService = $netopiaService;
    }

    /**
     * Create a subscription payment
     */
    public function createSubscriptionPayment(Request $request): JsonResponse
    {
        $request->validate([
            'plan_type' => 'required|in:premium',
        ]);

        $user = $request->user();
        $planType = $request->input('plan_type');

        try {
            // Check if user already has premium subscription
            $subscription = $user->activeSubscription;
            if ($subscription && $subscription->plan_type === 'premium' && $subscription->isActive()) {
                return response()->json([
                    'message' => 'Already have active premium subscription',
                    'subscription' => $subscription,
                ], 400);
            }

            // Create or get subscription
            if (!$subscription) {
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

            // Create payment with Netopia
            $result = $this->netopiaService->createSubscriptionPayment($user, $planType);

            if (!$result['success']) {
                return response()->json([
                    'message' => 'Payment creation failed',
                    'error' => $result['error'],
                ], 500);
            }

            return response()->json([
                'message' => 'Payment created successfully',
                'payment_id' => $result['payment_id'],
                'payment_url' => $result['payment_url'],
                'amount' => Subscription::PLANS[$planType]['price'],
                'currency' => 'EUR',
            ]);

        } catch (\Exception $e) {
            Log::error('Payment creation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Payment creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(Request $request, $paymentId): JsonResponse
    {
        $user = $request->user();
        $payment = Payment::where('id', $paymentId)
                         ->where('user_id', $user->id)
                         ->first();

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        return response()->json([
            'payment' => [
                'id' => $payment->id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'created_at' => $payment->created_at,
                'processed_at' => $payment->processed_at,
            ],
        ]);
    }

    /**
     * Handle Netopia webhook
     */
    public function webhook(Request $request): JsonResponse
    {
        $data = $request->all();

        Log::info('Netopia webhook received', $data);

        $result = $this->netopiaService->handleWebhook($data);

        return response()->json([
            'success' => $result,
            'message' => $result ? 'Webhook processed' : 'Webhook failed',
        ]);
    }

    /**
     * Payment success page
     */
    public function success(Request $request, $paymentId): JsonResponse
    {
        $payment = Payment::find($paymentId);

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        return response()->json([
            'message' => 'Payment successful',
            'payment' => [
                'id' => $payment->id,
                'status' => $payment->status,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
            ],
            'subscription' => $payment->subscription,
        ]);
    }

    /**
     * Get user's subscription info
     */
    public function getSubscription(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->activeSubscription;

        if (!$subscription) {
            return response()->json([
                'message' => 'No active subscription',
                'subscription' => null,
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
            ],
        ]);
    }
}
