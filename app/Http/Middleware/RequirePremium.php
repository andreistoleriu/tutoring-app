<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePremium
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

        if (!$subscription || $subscription->plan_type !== 'premium' || !$subscription->isActive()) {
            return response()->json([
                'message' => 'Premium subscription required',
                'subscription_required' => true,
                'current_plan' => $subscription?->plan_type ?? 'none',
            ], 403);
        }

        return $next($request);
    }
}
