<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdController extends Controller
{
    /**
     * Get ads for current user based on placement and type
     */
    public function getAds(Request $request): JsonResponse
    {
        $request->validate([
            'placement' => 'nullable|in:header,sidebar,footer,feed,modal',
            'type' => 'nullable|in:banner,card,popup',
            'limit' => 'nullable|integer|min:1|max:10',
        ]);

        $user = $request->user();

        // Check if user should see ads
        if ($user && !$user->shouldShowAds()) {
            return response()->json([
                'ads' => [],
                'should_show_ads' => false,
                'reason' => 'Premium user - no ads',
            ]);
        }

        // Determine target audience
        $audience = $this->determineAudience($user);
        $placement = $request->input('placement');
        $type = $request->input('type');
        $limit = $request->input('limit', 3);

        // Build query
        $query = Ad::active()->forAudience($audience)->priority();

        if ($placement) {
            $query->byPlacement($placement);
        }

        if ($type) {
            $query->byType($type);
        }

        // Get ads
        $ads = $query->limit($limit)
                    ->get()
                    ->map(function ($ad) {
                        // Record impression
                        $ad->recordImpression();

                        return [
                            'id' => $ad->id,
                            'title' => $ad->title,
                            'description' => $ad->description,
                            'image_url' => $ad->image_url,
                            'click_url' => $ad->click_url,  // Using your actual field name
                            'type' => $ad->type,
                            'placement' => $ad->placement,  // Using your actual field name
                            'target_audience' => $ad->target_audience, // From targeting JSON
                            'priority' => $ad->priority,
                        ];
                    });

        return response()->json([
            'ads' => $ads,
            'should_show_ads' => true,
            'user_type' => $user?->user_type ?? 'guest',
            'target_audience' => $audience,
            'requested_placement' => $placement,
            'requested_type' => $type,
        ]);
    }

    /**
     * Record ad click
     */
    public function recordClick(Request $request, $adId): JsonResponse
    {
        $ad = Ad::findOrFail($adId);

        if (!$ad->is_active || !$ad->hasStarted() || $ad->isExpired()) {
            return response()->json(['message' => 'Ad is not active'], 400);
        }

        $ad->recordClick();

        Log::info('Ad clicked', [
            'ad_id' => $ad->id,
            'ad_title' => $ad->title,
            'user_id' => $request->user()?->id,
            'user_type' => $request->user()?->user_type,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Click recorded successfully',
            'ad_id' => $ad->id,
            'redirect_url' => $ad->click_url,  // Using your actual field name
            'total_clicks' => $ad->clicks,
            'ctr' => $ad->ctr_percentage,
        ]);
    }

    /**
     * Get ad statistics (for admin/analytics)
     */
    public function getStats(Request $request): JsonResponse
    {
        $stats = [
            'total_ads' => Ad::count(),
            'active_ads' => Ad::active()->count(),
            'total_impressions' => Ad::sum('impressions'),
            'total_clicks' => Ad::sum('clicks'),
            'average_ctr' => Ad::where('impressions', '>', 0)
                ->selectRaw('AVG((clicks / impressions) * 100) as avg_ctr')
                ->value('avg_ctr'),
            'ads_by_type' => Ad::active()
                ->selectRaw('type, COUNT(*) as count, SUM(impressions) as impressions, SUM(clicks) as clicks')
                ->groupBy('type')
                ->get(),
            'ads_by_placement' => Ad::active()
                ->selectRaw('placement, COUNT(*) as count, SUM(impressions) as impressions, SUM(clicks) as clicks')
                ->groupBy('placement')
                ->get(),
        ];

        return response()->json([
            'stats' => $stats,
            'generated_at' => now(),
        ]);
    }

    /**
     * Determine target audience based on user
     */
    private function determineAudience($user): string
    {
        if (!$user) {
            return 'all';
        }

        // Check subscription status
        $subscription = $user->activeSubscription;

        if (!$subscription || $subscription->plan_type === 'free_trial') {
            return 'trial_users';
        }

        // Check user type
        if ($user->user_type === 'student') {
            return 'students';
        } elseif ($user->user_type === 'tutor') {
            return 'tutors';
        }

        return 'all';
    }
}
