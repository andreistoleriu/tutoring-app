<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Booking;
use App\Models\Tutor;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isTutor()) {
            return $this->tutorStats($user);
        } else {
            return $this->studentStats($user);
        }
    }

    private function tutorStats(User $user): JsonResponse
    {
        $tutor = $user->tutor;
        $thisMonth = now()->startOfMonth();

        $stats = [
            'total_earnings' => $tutor->total_earnings,
            'total_lessons' => $tutor->total_lessons,
            'average_rating' => round($tutor->rating, 1),
            'total_reviews' => $tutor->total_reviews,
            'this_month_bookings' => $user->tutorBookings()
                ->where('created_at', '>=', $thisMonth)->count(),
            'pending_bookings' => $user->tutorBookings()
                ->where('status', 'pending')->count(),
            'upcoming_lessons' => $user->tutorBookings()
                ->where('scheduled_at', '>', now())
                ->whereIn('status', ['pending', 'confirmed'])->count(),
            'pending_payments' => $user->tutorBookings()
                ->where('payment_method', 'cash')
                ->where('payment_status', 'pending')
                ->where('status', 'completed')
                ->sum('price'),
        ];

        return response()->json(['stats' => $stats]);
    }

    private function studentStats(User $user): JsonResponse
    {
        $thisMonth = now()->startOfMonth();

        $stats = [
            'total_bookings' => $user->studentBookings()->count(),
            'completed_lessons' => $user->studentBookings()
                ->where('status', 'completed')->count(),
            'this_month_bookings' => $user->studentBookings()
                ->where('created_at', '>=', $thisMonth)->count(),
            'total_spent' => $user->studentBookings()
                ->where('payment_status', 'paid')->sum('price'),
            'favorite_subjects' => $user->studentBookings()
                ->with('subject')
                ->selectRaw('subject_id, COUNT(*) as count')
                ->groupBy('subject_id')
                ->orderBy('count', 'desc')
                ->limit(3)
                ->get(),
            'pending_reviews' => $user->studentBookings()
                ->where('status', 'completed')
                ->whereDoesntHave('review')
                ->count(),
        ];

        return response()->json(['stats' => $stats]);
    }

    public function recentActivity(Request $request): JsonResponse
    {
        $user = $request->user();

        $recentBookings = $user->isTutor()
            ? $user->tutorBookings()
            : $user->studentBookings();

        $activity = $recentBookings
            ->with(['student', 'tutor.tutor', 'subject'])
            ->latest()
            ->limit(10)
            ->get();

        return response()->json(['activity' => $activity]);
    }
}
