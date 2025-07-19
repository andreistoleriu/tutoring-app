<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NetopiaService
{
    private $client;
    private $apiKey;
    private $posSignature;
    private $baseUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = config('services.netopia.api_key');
        $this->posSignature = config('services.netopia.pos_signature');
        $this->baseUrl = config('services.netopia.base_url');
    }

    /**
     * Create a subscription payment
     */
    public function createSubscriptionPayment(User $user, string $planType = 'premium'): array
    {
        try {
            $subscription = $user->activeSubscription ?? $user->subscription;
            $amount = Subscription::PLANS[$planType]['price'];

            $paymentId = 'SUBSCRIPTION_' . $user->id . '_' . time();

            // Create payment record
            $payment = Payment::create([
                'payment_id' => $paymentId,
                'subscription_id' => $subscription->id,
                'user_id' => $user->id,
                'amount' => $amount,
                'currency' => 'EUR',
                'payment_type' => 'subscription',
                'status' => 'pending',
                'payment_data' => [
                    'plan_type' => $planType,
                    'user_type' => $user->user_type,
                ],
            ]);

            // Prepare order data for Netopia
            $orderData = [
                'order' => [
                    'ntpID' => $paymentId,
                    'posSignature' => $this->posSignature,
                    'dateTime' => now()->format('Y-m-d H:i:s'),
                    'description' => "Premium Subscription - {$user->full_name}",
                    'orderID' => $payment->id,
                    'amount' => $amount,
                    'currency' => 'EUR',
                    'billing' => [
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'firstName' => $user->first_name,
                        'lastName' => $user->last_name,
                    ],
                    'shipping' => [
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'firstName' => $user->first_name,
                        'lastName' => $user->last_name,
                    ],
                    'products' => [
                        [
                            'name' => 'Premium Subscription (Monthly)',
                            'code' => 'PREMIUM_MONTHLY',
                            'category' => 'Subscription',
                            'price' => $amount,
                            'vat' => 0,
                        ]
                    ],
                ],
                'config' => [
                    'emailTemplate' => false,
                    'smsTemplate' => false,
                    'language' => 'ro',
                    'redirectUrl' => route('payment.success', ['payment' => $payment->id]),
                    'notifyUrl' => route('payment.webhook'),
                ],
            ];

            // Make API call to Netopia
            $response = $this->client->post($this->baseUrl . '/payment/card/start', [
                'json' => $orderData,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
            ]);

            $responseData = json_decode($response->getBody(), true);

            if (isset($responseData['error'])) {
                throw new \Exception('Netopia Error: ' . $responseData['error']['message']);
            }

            // Update payment with Netopia response
            $payment->update([
                'netopia_transaction_id' => $responseData['ntpID'] ?? null,
                'netopia_order_id' => $responseData['orderID'] ?? null,
                'netopia_response' => $responseData,
            ]);

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'payment_url' => $responseData['paymentURL'],
                'netopia_id' => $responseData['ntpID'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Netopia payment creation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle webhook from Netopia
     */
    public function handleWebhook(array $data): bool
    {
        try {
            $paymentId = $data['ntpID'] ?? null;
            $status = $data['status'] ?? null;

            if (!$paymentId) {
                Log::warning('Netopia webhook: No payment ID provided');
                return false;
            }

            $payment = Payment::where('netopia_transaction_id', $paymentId)->first();

            if (!$payment) {
                Log::warning('Netopia webhook: Payment not found', ['payment_id' => $paymentId]);
                return false;
            }

            // Update payment based on status
            switch ($status) {
                case 'confirmed':
                case 'paid':
                    $payment->markAsCompleted();
                    $this->activateSubscription($payment);
                    break;
                case 'failed':
                case 'cancelled':
                    $payment->markAsFailed($data['message'] ?? 'Payment failed');
                    break;
                default:
                    Log::info('Netopia webhook: Unknown status', ['status' => $status]);
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Netopia webhook handling failed', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return false;
        }
    }

    /**
     * Activate subscription after successful payment
     */
    private function activateSubscription(Payment $payment): void
    {
        $subscription = $payment->subscription;
        $planType = $payment->payment_data['plan_type'] ?? 'premium';

        $subscription->update([
            'plan_type' => $planType,
            'price' => $payment->amount,
            'status' => 'active',
            'shows_ads' => false,
            'expires_at' => now()->addDays(30),
            'netopia_subscription_id' => $payment->netopia_transaction_id,
        ]);

        Log::info('Subscription activated', [
            'user_id' => $payment->user_id,
            'subscription_id' => $subscription->id,
            'plan_type' => $planType,
        ]);
    }
}
