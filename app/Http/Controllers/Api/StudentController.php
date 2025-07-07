<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isStudent()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Get upcoming bookings
        $upcomingBookings = $user->studentBookings()
            ->with(['tutor.tutor', 'subject'])
            ->where('scheduled_at', '>', now())
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get();

        // Get recent bookings
        $recentBookings = $user->studentBookings()
            ->with(['tutor.tutor', 'subject'])
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->limit(5)
            ->get();

        // Calculate stats
        $thisMonth = now()->startOfMonth();
        $stats = [
            'total_bookings' => $user->studentBookings()->count(),
            'completed_lessons' => $user->studentBookings()
                ->where('status', 'completed')->count(),
            'this_month_bookings' => $user->studentBookings()
                ->where('created_at', '>=', $thisMonth)->count(),
            'total_spent' => $user->studentBookings()
                ->where('payment_status', 'paid')->sum('price'),
            'pending_reviews' => $user->studentBookings()
                ->where('status', 'completed')
                ->whereDoesntHave('review')
                ->count(),
        ];

        return response()->json([
            'stats' => $stats,
            'upcoming_bookings' => $upcomingBookings,
            'recent_bookings' => $recentBookings,
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'phone' => 'nullable|string|regex:/^(\+40|0)[0-9]{9}$/',
        ]);

        $request->user()->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $request->user(),
        ]);
    }
}
