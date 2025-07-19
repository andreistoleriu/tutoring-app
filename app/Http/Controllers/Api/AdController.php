<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdController extends Controller
{
    /**
     * Get ads for current user
     */
    public function getAds(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check if user should see ads
        if (!$user->shouldShowAds()) {
            return response()->json([
                'ads' => [],
                'should_show_ads' => false,
            ]);
        }

        // Determine audience
        $audience = 'all';
        if ($user->isInTrial()) {
            $audience = 'trial_users';
        } elseif ($user->isStudent()) {
            $audience = 'students';
        } elseif ($user->isTutor()) {
            $audience = 'tutors';
        }

        // Get ads
        $ads = Ad::active()
                ->forAudience($audience)
                ->inRandomOrder()
                ->limit(3)
                ->get(['id', 'title', 'description', 'image_url', 'link_url', 'type']);

        // Record impressions
        foreach ($ads as $ad) {
            $ad->recordImpression();
        }

        return response()->json([
            'ads' => $ads,
            'should_show_ads' => true,
            'user_type' => $user->user_type,
            'subscription_status' => $user->activeSubscription?->status ?? 'none',
        ]);
    }

    /**
     * Record ad click
     */
    public function recordClick(Request $request, $adId): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $ad = Ad::find($adId);

        if (!$ad) {
            return response()->json(['message' => 'Ad not found'], 404);
        }

        $ad->recordClick();

        return response()->json([
            'message' => 'Click recorded',
            'ad_id' => $ad->id,
            'total_clicks' => $ad->clicks,
        ]);
    }
}
